<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 **/

require_once(APPPATH.'libraries/tropo/tropo.class.php');
require_once(APPPATH.'libraries/VoiceVault.php');

class TropoJSON_Enroll extends MY_Controller {

	private $data = array();
	protected $user_id;

	private $tropo;
	private $tropo_session;

	private $initialData;

	private $voice_vault;

	private $user;
	
	public function __construct()
	{
		parent::__construct();
		$this->config->load('openvbx');
		$this->load->database();
		$this->load->model('vbx_device');

		$this->user_id = $this->session->userdata('user_id');

		$this->tropo = new Tropo;
		try {
			$this->tropo_session = new Session_Tropo;
			$this->session->set_userdata(array('tropo-session'=>
				file_get_contents('php://input')));
			$_COOKIE['tropo_session'] = file_get_contents('php://input');
			set_cookie('tropo_session', 
				file_get_contents('php://input'),
				0);
		} catch (TropoException $e) {
			$sessionData = $this->session->userdata('tropo-session');
			if ($sessionData) {
				// Session not available
				$this->tropo_session = new Session_Tropo(
					$this->session->userdata('tropo-session'));
			}
		}

		$from = normalize_phone_to_E164($this->session->userdata('from'));
		$to = normalize_phone_to_E164($this->session->userdata('to'));
		$user_id = $this->session->userdata('user_id');

		$this->initialData = array(
			'from'=>$from,
			'to'=>$to,
			'user_id'=>$user_id
		);

		// Grab the user's voice vault information
		$user = VBX_User::get(array('id'=>$user_id));
		$settings = new VBX_Settings;
		$voicevault_username = $settings->get('voicevault_username', $user->tenant_id);
		$voicevault_password = $settings->get('voicevault_password', $user->tenant_id);
		$voicevault_config = $settings->get('voicevault_config', $user->tenant_id);
		$voicevault_organisation = $settings->get('voicevault_organisation', $user->tenant_id);

		$this->user = $user;

		// Initiate voice vault
		if ($voicevault_username && $voicevault_password 
			&& $voicevault_config && $voicevault_organisation)
		{
			$this->voice_vault = new VoiceVault($voicevault_username,
				$voicevault_password,
				$voicevault_organisation,
				$voicevault_config);
		}
	}

	public function index()
	{
		if ($this->voice_vault) {
			// Register the claimant before we do anything (less processing time)
			$claimantResult = $this->voice_vault->RegisterClaimant();
			if ($claimantResult->status_code == 0) {
				$this->log('Claimant success.');
				$claimantID = strval($claimantResult->claimant_id);
				$this->session->set_userdata('claimant_id', $claimantID);

				// Start dialog
				$dialogueResult = $this->voice_vault->StartDialogue($claimantID);

				if ($dialogueResult->status_code <> 0) {
					$this->log('Dialogue Failed');
					$this->log($dialogueResult);
					return;
				}

				$this->session->set_userdata('prompt', strval($dialogueResult->prompt_hint));
				$this->session->set_userdata('dialogue_id', strval($dialogueResult->dialogue_id));
			} else {
				$this->log('Claimant Failed');
				$this->log($claimantResult);
				return;
			}
		}

		if ($this->initialData) {
			if ($this->initialData['from'] &&
			$this->initialData['to'] &&
			$this->initialData['user_id']) {
				$this->tropo->on(array(
					'event'=>'continue',
					'next'=>site_url('voicevault/tropojson/enroll/call')
				));
				$this->tropo->renderJSON();
				return;
			}
		}
		$this->tropo->say("Error: Could not place Tropo call.");

		$this->tropo->renderJSON();
	}

	public function call()
	{
		// Place the initial outbound call.
		$this->tropo->call($this->initialData['to'],
			array('from'=>$this->initialData['from']));
		$this->tropo->on(array(
			'event'=>'continue',
			'next'=>'welcome',
		));

		$this->tropo->renderJSON();
	}

	public function welcome()
	{
		// First, check to make sure the user has a VoiceVault account setup.
		if (!$this->voice_vault)
		{
			$this->tropo->say("Error: VoiceVault account not set up.");
			$this->tropo->renderJSON();
			return;
		}

		$this->tropo->say("Welcome to the Voice Vault enrollment system. Please follow the prompts, speaking the answer clearly. If you don't speak clearly enough, future verification can fail.");

		$this->tropo->on(array(
			'event'=>'continue',
			'next'=>'prompt'
		));

		$this->tropo->renderJSON();
	}

	public function prompt()
	{
		$prompt = $this->session->userdata('prompt');

		$sayText = "<speak>";
		$sayText .= "Please say the following prompt: ";
		$sayText .= "<say-as interpret-as='vxml:digits'>";
		$sayText .= $prompt;
		$sayText .= "</say-as>";
		$sayText .= "</speak>";

		if ($this->tropo_session)
			$sessionID = $this->tropo_session->getId();
		else
			$sessionID = 'session';

		$this->tropo->record(array(
			'say'=>$sayText, 
			'url'=>
				site_url('voicevault/tropojson/enroll/upload')
				."?sessionID=$sessionID",
			'choices'=>'#'));

		$this->tropo->on(array(
			'event'=>'continue',
			'next'=>'process_prompt'));

		$this->tropo->renderJSON();
	}

	public function process_prompt()
	{
		// Post the file to the vault
		if ($this->tropo_session)
			$sessionID = $this->tropo_session->getId();
		else
			$sessionID = 'session';
		$target = dirname(__FILE__)."/../../../audio-uploads/$sessionID.wav";
		$prompt = $this->session->userdata('prompt');
		$dialogue_id = $this->session->userdata('dialogue_id');
		$submitResult = $this->voice_vault->SubmitPhrase(
			$target,
			$prompt,
			$dialogue_id
		);

		unlink($target);

		if ($submitResult->status_code == 0) {
			$this->session->set_userdata('prompt', strval($submitResult->prompt_hint));

			$this->log('Phrase submitted');
			$this->log($submitResult);

			if ($submitResult->dialogue_status == 'Started') {
				switch ($submitResult->request_status) 
				{
					case 'OK':
						$this->tropo->on(array('event'=>'continue',
							'next'=>'prompt'));
						break;
					case 'TooManyUnprocessedPhrases':
						// App is currently processing, call GetDialogueSummary
						$this->tropo->on(array('event'=>'continue',
							'next'=>'checkdialogue'));
						break;
					
					default:
						// ERROR
						die(var_dump($submitResult));
						$this->tropo->say("Invalid request status.");
						$this->tropo->renderJSON();
						return;
						break;
				}
			} else if ($submitResult->dialogue_status == 'Succeeded') {
				$this->log('Dialogue done!');

				// Enrollment succeeded
				$this->tropo->on(array('event'=>'continue',
					'next'=>'done'));
			} else if ($submitResult->dialogue_status == 'Failed') {
				// Enrollment failed
				$this->tropo->on(array('event'=>'continue',
					'next'=>'failed'));
			}
			$this->tropo->say("Prompt upload successful.");
		} else {
			$this->log('Phrase Submission Failed');
			$this->log($submitResult);

			$this->tropo->say("Invalid result.");
			$this->tropo->renderJSON();
			return;
		}

		$this->tropo->renderJSON();
	}

	public function checkdialogue()
	{
		$dialogue_id = $this->session->userdata('dialogue_id');
		$dialogueStatus = $this->voice_vault->GetDialogueSummary($dialogue_id);

		$this->log($dialogueStatus);

		if ($dialogueStatus->status_code == 0) {
			switch ($dialogueStatus->dialogue_status) 
			{
				case 'Started':
					if ($dialogueStatus->request_status == 'OK') {
						$this->session->set_userdata('prompt', 
							strval($dialogueStatus->prompt_hint));

						// More phrases are needed
						$this->log('More phrases needed.');
						$this->tropo->on(array('event'=>'continue',
							'next'=>'prompt'));
					} else if ($dialogueStatus->request_status ==
							'TooManyUnprocessedPhrases') {
						// Continue to poll dialogue summary
						$this->tropo->on(array('event'=>'continue',
							'next'=>'checkdialogue'));
					} else {
						// Error
						// $this->log('Invalid Dialogue Summary');
						// $this->log($dialogueStatus);
						die(var_dump($dialogueStatus));
					}
					break;

				case 'Succeeded':
					// Enrollment complete
					$this->tropo->on(array('event'=>'continue',
						'next'=>'done'));
					break;
				
				default:
					// Error
					// $this->log('Invalid Dialogue Summary');
					// $this->log($dialogueStatus);
					die(var_dump($dialogueStatus));
					break;
			}
		} else {
			// $this->log('Invalid Dialogue Summary');
			// $this->log($dialogueStatus);
			die(var_dump($dialogueStatus));
		}
		$this->tropo->renderJSON();
	}

	public function done()
	{
		// Store the claimant in our system
		$claimant_id = $this->session->userdata('claimant_id');
		$user_data = unserialize($this->user->data);
		if (!$user_data) {
			$user_data = array();
		}
		$user_data['claimant_id'] = $claimant_id;
		$this->user->data = serialize($user_data);
		$this->user->save();

		$this->tropo->say("Finished with Voice Vault enrollment. Goodbye.");
		$this->tropo->renderJSON();
	}

	public function failed()
	{
		$this->tropo->say("Enrollment failed. Please try again. Make sure you speak clearly enough, or the verification will fail. Goodbye.");
		$this->tropo->renderJSON();
	}

	function log($value)
	{
		// $f = fopen('voicevault.log', 'a');

		// if ($f)
		// {
		// 	if (is_string($value))
		// 		fwrite($f, $value);
		// 	else
		// 		fwrite($f, var_export($value, true));

		// 	fclose($f);
		// }
	}


	public function upload()
	{
		$sessionID = $this->input->get('sessionID');
		$target = dirname(__FILE__)."/../../../audio-uploads/$sessionID.wav";
		move_uploaded_file($_FILES['filename']['tmp_name'], $target);
	}
}
