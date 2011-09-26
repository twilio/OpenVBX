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

require_once(APPPATH.'libraries/twilio.php');
require_once(APPPATH.'libraries/VoiceVault.php');

class Twiml_Enroll extends MY_Controller {

	protected $response;
	protected $request;

	protected $user_id;
	private $initialData;
	private $user;

	private $voice_vault;
	
	public function __construct()
	{
		parent::__construct();

		if (isset($_GET['from'])) 
			$this->session->set_userdata('from', $_GET['from']);
		if (isset($_GET['to']))
			$this->session->set_userdata('to', $_GET['to']);
		if (isset($_GET['user_id']))
			$this->session->set_userdata('user_id', $_GET['user_id']);

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

		// Twilio
		$this->request = new TwilioUtils($this->twilio_sid, $this->twilio_token);
		$this->response = new Response();
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
				$this->response->addRedirect(
					site_url('voicevault/twiml/enroll/welcome'));
				return $this->response->Respond();
			}
		}
		$this->response->addSay("Error: Could not place Tropo call.");

		return $this->response->Respond();
	}

	public function welcome()
	{
		// First, check to make sure the user has a VoiceVault account setup.
		if (!$this->voice_vault)
		{
			$this->response->addSay("Error: VoiceVault account not set up.");
			return $this->response->Respond();
		}

		$this->response->addSay("Welcome to the Voice Vault enrollment system. Please follow the prompts, speaking the answer clearly. If you don't speak clearly enough, future verification can fail.");

		$this->response->addRedirect('prompt');

		return $this->response->Respond();
	}

	public function prompt()
	{
		$prompt = $this->session->userdata('prompt');

		$sayText .= "Please say the following prompt: ";
		$sayText .= implode(' ', str_split($prompt));

		if ($this->request->CallSid)
			$sessionID = $this->request->CallSid;
		else
			$sessionID = 'session';

		$this->response->addSay($sayText);
		$this->response->addRecord(array(
			'action'=>site_url('voicevault/twiml/enroll/process_prompt')));
		$this->response->addRedirect('prompt');

		return $this->response->Respond();
	}

	public function process_prompt()
	{
		// Post the file to the vault
		if ($this->request->CallSid)
			$sessionID = $this->request->CallSid;
		else
			$sessionID = 'session';

		$target = dirname(__FILE__)."/../../../audio-uploads/$sessionID.wav";

		// Copy the file from twilio's servers locally
		$data = file_get_contents($this->request->RecordingUrl);
		$fp = fopen($target, "w+");
		fwrite($fp, $data);
		fclose($fp);

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
						$this->response->addRedirect('prompt');
						break;
					case 'TooManyUnprocessedPhrases':
						// App is currently processing, call GetDialogueSummary
						$this->response->addRedirect('checkdialogue');
						break;
					
					default:
						// ERROR
						die(var_dump($submitResult));
						$this->response->addSay("Invalid request status.");
						$this->response->Respond();
						return;
						break;
				}
			} else if ($submitResult->dialogue_status == 'Succeeded') {
				$this->log('Dialogue done!');
				// Enrollment succeeded
				$this->response->addRedirect('done');
			} else if ($submitResult->dialogue_status == 'Failed') {
				// Enrollment failed
				$this->response->addRedirect('failed');
			}
			$this->response->addSay("Prompt upload successful.");
		} else {
			$this->log('Phrase Submission Failed');
			$this->log($submitResult);

			$this->response->addSay("Invalid result.");
			$this->response->Respond();
			return;
		}

		$this->response->Respond();
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
						$this->response->addRedirect('prompt');
					} else if ($dialogueStatus->request_status ==
							'TooManyUnprocessedPhrases') {
						// Continue to poll dialogue summary
						$this->response->addRedirect('checkdialogue');
					} else {
						// Error
						// $this->log('Invalid Dialogue Summary');
						// $this->log($dialogueStatus);
						die(var_dump($dialogueStatus));
					}
					break;

				case 'Succeeded':
					// Enrollment complete
					$this->response->addRedirect('done');
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
		$this->response->Respond();
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

		$this->response->addSay("Finished with Voice Vault enrollment. Goodbye.");
		$this->response->Respond();
	}

	public function failed()
	{
		$this->response->addSay("Enrollment failed. Please try again. Make sure you speak clearly enough, or the verification will fail. Goodbye.");
		$this->response->Respond();
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
}
