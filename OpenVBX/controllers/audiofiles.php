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

include_once(APPPATH.'libraries/twilio.php');

class AudioFilesException extends Exception {}

class AudioFiles extends User_Controller
{
	protected $response;
	protected $request;

	function __construct()
	{
		parent::__construct();
		$this->load->model('vbx_audio_file');
	}

	function index()
	{
		$this->respond('', 'library', $data);
	}

	function add_file()
	{
		$json = array(
			'error' => false,
			'message' => ''
		);

		if (!empty($_FILES) && isset($_FILES["Filedata"]) && $_FILES["Filedata"]["error"] == UPLOAD_ERR_OK)
		{
			$file = $_FILES["Filedata"];

			$name_parts = explode('.', $file['name']);
			$ext = $name_parts[count($name_parts) - 1];

			if (in_array(strtolower($ext), array('wav', 'mp3')))
			{
				// Can we write to our audio upload directory?
				$audioUploadsPath = 'audio-uploads/';

				if (is_writable($audioUploadsPath))
				{
					$targetFile = null;

					// Make sure we pick a name that's not already in use...
					while ($targetFile == null)
					{
						$candidate = $audioUploadsPath . md5(uniqid($file['name'])) . '.' . $ext;

						if (!file_exists($candidate))
						{
							// We can use this filename
							$targetFile = $candidate;
							break;
						}
					}

					move_uploaded_file($file['tmp_name'], $targetFile);

					// Return the URL for our newly created file
					$json['url'] = "vbx-audio-upload://" . basename($targetFile);

					$ci =& get_instance();

					// And, make a record in the database
					$audioFile = new VBX_Audio_File();
					$audioFile->label = "Upload of " . $file['name'];
					$audioFile->user_id = intval($this->session->userdata('user_id'));
					$audioFile->url = $json['url'];
					$audioFile->tag = $this->input->post('tag');
					$audioFile->save();

					// We return the label so that this upload can be added the library UI without
					// refreshing the page.
					$json['label'] = $audioFile->label;
				}
				else
				{
					$json['error'] = true;
					$json['message'] = 'Upload directory is not writable';
				}
			}
			else
			{
				$json['error'] = true;
				$json['message'] = 'Unsupported file format.  Only MP3 and WAV files are supported.';
			}
		}
		else
		{
			$json['error'] = true;
			$json['message'] = 'No files were found in the upload.';
			if(isset($_FILES["Filedata"]))
			{
				$error = $_FILES["Filedata"]["error"];
				switch ($error)
				{
					case UPLOAD_ERR_INI_SIZE:
						$json['message'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
						break;
					case UPLOAD_ERR_FORM_SIZE:
						$json['message'] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
						break;
					case UPLOAD_ERR_PARTIAL:
						$json['message'] = 'The uploaded file was only partially uploaded';
						break;
					case UPLOAD_ERR_NO_FILE:
						$json['message'] = 'No file was uploaded';
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$json['message'] = 'Missing a temporary folder';
						break;
					case UPLOAD_ERR_CANT_WRITE:
						$json['message'] = 'Failed to write file to disk';
						break;
					case UPLOAD_ERR_EXTENSION:
						$json['message'] = 'File upload stopped by extension';
						break;
				}
			}
		}

		$data = array();
		$data['json'] = $json;
		$this->response_type = 'json';
		$this->respond('', null, $data);
	}

	function add_from_twilio_recording()
	{
		$json = array(
			'error' => false,
			'message' => ''
		);

		$to = $this->input->post('to');
		$callerid = $this->input->post('callerid');

		if (strlen($to) == 0)
		{
			$json['error'] = true;
			$json['message'] = "You must provide a number to call.";
		}
		else if (strlen($callerid) == 0)
		{
			$json['error'] = true;
			$json['message'] = "You must have an incoming number to record a greeting. <a href=\"".site_url('numbers')."\">Get a Number</a>";
		}
		else
		{
			$rest_access_token = $this->make_rest_access();
			$path = 'audiofiles!prompt_for_recording_twiml';
			$recording_url = stripslashes(site_url("twiml/redirect/" . $path . "/$rest_access_token"));

			$service = OpenVBX::getService();
			
			try {
				$call = $service->account->calls->create(
														$callerid,
														$to,
														$recording_url
													);
// ep($call->sid);
				// Create a place holder for our recording
				$audioFile = new VBX_Audio_File((object) Array(
						'label' => 'Recording with '.format_phone($to),
						'user_id' => intval($this->session->userdata('user_id')),
						'recording_call_sid' => $call->sid,
						'tag' => $this->input->post('tag')
					));
				$audioFile->save();

				$json['id'] = $audioFile->id;
			}
			catch (Exception $e) {
				$json['message'] = $e->getMessage();
				$json['error'] = true;
			}
		}

		$data = array();
		$data['json'] = $json;
		$this->response_type = 'json';
		$this->respond('', null, $data);
	}

	function prompt_for_recording_twiml()
	{
		if (!OpenVBX::validateRequest()) {
			$response = new TwimlResponse;
			$response->say('Could not validate this request. Goodbye');
			$response->respond();
		}

		$response = new TwimlResponse;
		$audioFile = VBX_Audio_File::get(array('recording_call_sid' => $this->input->get_post('CallSid')));

		if (!$audioFile->cancelled)
		{
			$response->say("Re-chord your message after the beep, press the pound key when finished.");
			$response->record(array('action' => site_url('audiofiles/replay_recording_twiml')));
			$response->say("We didn't get a recording from you, try again.");
			$response->redirect(site_url('audiofiles/prompt_for_recording_twiml'));
		}
		else
		{
			$response->say("The recording was cancelled.");
			$response->hangup();
		}

		return $response->respond();
	}

	function replay_recording_twiml()
	{
		$response = new TwimlResponse();

		if ($this->input->get_post('RecordingUrl'))
		{
			// Stuff this in our session.  We'll come get it later when it's time to save!
			$recording = $this->input->get_post('RecordingUrl') . '.mp3';
			$this->session->set_userdata('current-recording', $recording);
		}

		$response->pause(array('length' => 1));
		$response->say('Recorded the following: ');
		$gather = $response->gather(array('numDigits' => 1,
										  'method' => 'POST',
										  'action' => site_url('audiofiles/accept_or_reject_recording_twiml')
										));
		$gather->play($this->session->userdata('current-recording'));
		$gather->say('If you like this message, press 1. ... To record a different message, press 2.');

		// If they don't enter anything at the prompt, do the replay again.
		$response->redirect(site_url('audiofiles/replay_recording_twiml'));

		return $response->respond();
	}

	function accept_or_reject_recording_twiml()
	{
		$this->request = new TwilioUtils($this->twilio_sid, $this->twilio_token);
		$this->response = new Response();

		switch($this->request->Digits)
		{
			case 1:
				$audioFile = VBX_Audio_File::get(array('recording_call_sid' => $this->request->CallSid));

				if ($audioFile == null)
				{
					trigger_error("That's weird - we can't find the place holder audio file that matches this sid (" . $this->request->CallSid . ")");
				}
				else
				{
					$audioFile->url = $this->session->userdata('current-recording');
					$audioFile->save();
				}

				$this->response->addSay('Your recording has been saved.');
				$this->response->addHangup();
				break;
			case 2:
				$this->response->addRedirect(site_url('audiofiles/prompt_for_recording_twiml'));
			default:
				$this->response->addRedirect(site_url('audiofiles/replay_recording_twiml'));
				break;
		}

		return $this->response->Respond();
	}

	function hangup_on_cancel()
	{
		// $this->request = new TwilioUtils($this->twilio_sid, $this->twilio_token);
		$this->response = new Response();

		$this->response->addHangup();

		return $this->response->Respond();
	}

	function cancel_recording()
	{
		$json = array(
			'error' => false,
			'message' => ''
		);

		$audio_file_id = $this->input->post('id');

		if (strlen($audio_file_id) == 0)
		{
			$json['error'] = true;
			$json['message'] = "Missing 'id' parameter.";
		}
		else
		{
			$audioFile = VBX_Audio_File::get($audio_file_id);

			if (is_null($audioFile))
			{
				trigger_error("We were given an id for an audio_file, but we can't find the record.	 That's odd.  And, by odd I really mean it should *never* happen.");
			}
			else if ($audioFile->user_id != $this->session->userdata('user_id'))
			{
				trigger_error("You can't cancel a recording you didn't start.");
			}
			else
			{
				error_log("Redirecting to cancel page!");
				
				$cancel_url = site_url('audiofiles/hangup_on_cancel');
				$service = OpenVBX::getService();
				
// ep($audioFile->recording_call_sid);
				
				try {
					$call = $service->account->calls->get($audioFile->recording_call_sid);
// ep($call->status);
					$call->route($cancel_url);
					$audioFile->cancelled = true;
					$audioFile->save();
				}
				catch (Exception $e) {
					ep($e->getMessage());
					//throw new AudioFilesException($e->getMessage());
					trigger_error($e->getMessage());
				}
			}
		}

		$data = array();
		$data['json'] = $json;
		$this->response_type = 'json';
		$this->respond('', null, $data);
	}

	function check_if_recording_is_finished()
	{
		$json = array(
			'error' => false,
			'message' => ''
		);

		$audio_file_id = $this->input->post('id');

		if (strlen($audio_file_id) == 0)
		{
			$json['error'] = true;
			$json['message'] = "Missing 'id' parameter.";
		}
		else
		{
			$newAudioFile = VBX_Audio_File::get($audio_file_id);

			if ($newAudioFile == null)
			{
				trigger_error("We were given an id for an audio_file, but we can't find the record.	 That's odd.  And, by odd I really mean it should *never* happen.");
			}
			else
			{
				if (strlen($newAudioFile->url) > 0)
				{
					$json['finished'] = true;
					$json['url'] = $newAudioFile->url;

					// We return the label so that this upload can be added the library UI without
					// refreshing the page.
					$json['label'] = $newAudioFile->label;
				}
				else
				{
					$json['finished'] = false;
				}
			}
		}

		$data = array();
		$data['json'] = $json;
		$this->response_type = 'json';
		$this->respond('', null, $data);
	}
}
