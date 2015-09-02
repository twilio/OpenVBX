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
	protected $say_params;
	
	protected $suppress_warnings_notices = true;
	
	function __construct()
	{	
		// This is to support SWFUpload. SWFUpload will scrape all cookies via Javascript 
		// and send them as POST request params. This enables the file uploader to work 
		// with a proper session.
		foreach ($_POST as $key => $value)
		{
			// Copy any key that looks like an Openvbx session over to $_COOKIE where it's expected
			if (preg_match("/^(\d+\-)?openvbx_session$/", $key))
			{
				// url-decode the session key, try to preserve "+" in email addresses
				$value = $_POST[$key];
				
				$preserve_plus = false;
				preg_match("|s:5:\"email\";s:[0-9]+:\"(.*?)\";|", $value, $matches);
				if (strpos($matches[1], '+') !== false)
				{
					$plus_temp = '___plus___';
					$preserve_plus = true;
					$email = str_replace('+', $plus_temp, $matches[0]);
					$value = str_replace($matches[0], $email, $value);
				}
				
				$value = urldecode($value);
				
				if ($preserve_plus)
				{
					$value = str_replace($plus_temp, '+', $value);
				}
				
				$_COOKIE[$key] = $value;
			}
		}

		parent::__construct();
		$this->load->library('TwimlResponse');
		$this->load->model('vbx_audio_file');
		$this->say_params = array(
			'voice' => $this->vbx_settings->get('voice', $this->tenant->id),
			'language' => $this->vbx_settings->get('voice_language', $this->tenant->id)
		);
	}

	function index()
	{
		$this->respond('', 'library', array());
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
			
			try {
				$account = OpenVBX::getAccount();
				$call = $account->calls->create(
											$callerid,
											$to,
											$recording_url
										);

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
		validate_rest_request();
		
		$response = new TwimlResponse;
		$audioFile = VBX_Audio_File::get(array('recording_call_sid' => $this->input->get_post('CallSid')));

		if (!$audioFile->cancelled)
		{
			$response->say("Re-chord your message after the beep, press the pound key when finished.", $this->say_params);
			$response->record(array('action' => site_url('audiofiles/replay_recording_twiml')));
			$response->say("We didn't get a recording from you, try again.", $this->say_params);
			$response->redirect(site_url('audiofiles/prompt_for_recording_twiml'));
		}
		else
		{
			$response->say("The recording was cancelled.", $this->say_params);
			$response->hangup();
		}

		return $response->respond();
	}

	function replay_recording_twiml()
	{
		validate_rest_request();
		
		$response = new TwimlResponse;

		if ($this->input->get_post('RecordingUrl'))
		{
			// Stuff this in our session.  We'll come get it later when it's time to save!
			$recording = $this->input->get_post('RecordingUrl') . '.mp3';
			$this->session->set_userdata('current-recording', $recording);
		}

		$response->pause(array('length' => 1));
		$response->say('Recorded the following: ', $this->say_params);
		$gather = $response->gather(array('numDigits' => 1,
										  'method' => 'POST',
										  'action' => site_url('audiofiles/accept_or_reject_recording_twiml')
										));
		$gather->play($this->session->userdata('current-recording'));
		$gather->say('If you like this message, press 1. ... To record a different message, press 2.', $this->say_params);

		// If they don't enter anything at the prompt, do the replay again.
		$response->redirect(site_url('audiofiles/replay_recording_twiml'));

		return $response->respond();
	}

	function accept_or_reject_recording_twiml()
	{
		validate_rest_request();
		
		$response = new TwimlResponse;
		$digits = clean_digits($this->input->get_post('Digits'));
		$call_sid = $this->input->get_post('CallSid');
		
		switch($digits)
		{
			case 1:
				$audioFile = VBX_Audio_File::get(array('recording_call_sid' => $call_sid));

				if ($audioFile == null)
				{
					trigger_error("That's weird - we can't find the place holder audio file that matches this sid (".$call_sid.")");
				}
				else
				{
					$audioFile->url = $this->session->userdata('current-recording');
					$audioFile->save();
				}

				$response->say('Your recording has been saved.', $this->say_params);
				$response->hangup();
				break;
			case 2:
				$response->redirect(site_url('audiofiles/prompt_for_recording_twiml'));
				break;
			default:
				$response->redirect(site_url('audiofiles/replay_recording_twiml'));
				break;
		}

		return $response->respond();
	}

	function hangup_on_cancel()
	{
		_deprecated_method(__METHOD__, '1.0.4');
		
		validate_rest_request();
		
		$response = new TwimlResponse;
		$response->hangup();
		return $response->respond();
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
				trigger_error("We were given an id for an audio_file, but we can't find the record. That's odd. And, by odd I really mean it should *never* happen.");
			}
			else if ($audioFile->user_id != $this->session->userdata('user_id'))
			{
				trigger_error("You can't cancel a recording you didn't start.");
			}
			else
			{
				log_message('debug', 'canceling call');
				try {
					$account = OpenVBX::getAccount();
					$call = $account->calls->get($audioFile->recording_call_sid);
									
					if ($call->status == 'ringing') {
						$params = array(
							'Status' => 'canceled'
						);
					}
					else {
						$params = array(
							'Status' => 'completed'
						);
					}
					
					$call->update($params);
					
					$audioFile->cancelled = true;
					$audioFile->save();
				}
				catch (Exception $e) {
					trigger_error($e->getMessage());
					$json['error'] = true;
					$json['message'] = $e->getMessage();
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
