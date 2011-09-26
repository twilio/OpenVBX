<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); /**
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

	public function test()
	{
		$sessionId = @$_GET['session'];

		if (!$sessionId)
			die('Invalid session ID.');

		// $file = VBX_Audio_File::get(array('recording_call_sid'=>$sessionId));
		// $file = VBX_Audio_File::get(array('id'=>21));
		$this->db->select('*');
		$this->db->from('audio_files');
		$this->db->where(array('id'=>21));
		
		$file = $this->db->get()->result();

		die(var_dump($file));
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

	/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
	// This method is now a alias for the new (more meaningful) 
	// "add_recording" method
	/** End Disruptive Technologies code **/
	function add_from_twilio_recording()
	{
		return $this->add_recording();
	}

	function add_recording()
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
			// Try twilio then tropo for the number
			$useTropo = false;
			$rest_access_token = $this->make_rest_access();

			$twilio = new TwilioRestClient($this->twilio_sid,
										   $this->twilio_token,
										   $this->twilio_endpoint);

			$path = 'audiofiles!prompt_for_recording_twiml';
			$recording_url = stripslashes(site_url("twiml/redirect/" . $path . "/$rest_access_token"));

			$response = $twilio->request("Accounts/{$this->twilio_sid}/Calls",
										 'POST',
										 array( "From" => $callerid,
												"To" => $to,
												"Url" => $recording_url
												)
										 );

			if ($response->IsError)
			{
				if (strpos($response->ErrorMessage, 'is not yet verified')
				|| strpos($response->ErrorMessage, 'resource was not found')) {
					// use tropo 
					$useTropo = true;
				}
				$json['message'] = $response->ErrorMessage;
				$json['error'] = true;
			}
			else
			{
				$callSid = $response->ResponseXml->Call->Sid[0];

				$ci =& get_instance();

				// Create a place holder for our recording
				$audioFile = new VBX_Audio_File();
				$audioFile->label = "Recording with " . format_phone($to);
				$audioFile->user_id = intval($this->session->userdata('user_id'));
				$audioFile->recording_call_sid = "$callSid";
				$audioFile->tag = $this->input->post('tag');
				$audioFile->save();

				$json['id'] = $audioFile->id;
			}

			if ($useTropo) {
				// try using tropo for the phone number
				if ($this->tropo_username && $this->tropo_password) {
					try {
						require_once(APPPATH.'libraries/tropo/tropo.class.php');
						$provisioner = new ProvisioningAPI(
							$this->tropo_username,
							$this->tropo_password);
						$applications = json_decode($provisioner->viewApplications());

						// just get the first available phone number
						foreach ($applications as $application) {
							$callerid = null;
							$voiceToken = null;

							$numbersResult = json_decode($provisioner->viewAddresses($application->id));

							if (count($numbersResult) > 0) {
								// Get first number
								foreach ($numbersResult as $number) {
									if ($number->type == 'number') {
										$callerid = $number->number;
									} else if ($number->type == 'token'
									&& $number->channel == 'voice') {
										$voiceToken = $number->token;
									}
									if ($callerid && $voiceToken)
										break;
								}
							}
							if ($callerid && $voiceToken)
								break;
						}

						// Mark request as successful
						if ($callerid && $voiceToken) {
							// Initiate outbound call
					        $ch = curl_init("http://api.tropo.com/1.0/sessions?action=create&token=$voiceToken&to=".urlencode($to)."&from=".urlencode($callerid)."&type=recordGreeting");
					        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					        $response = curl_exec($ch);
					        curl_close($ch);

							if ($response &&
							 strpos($response, '<session>') !== false) {
							 	$responseObj = new SimpleXMLElement($response);
								$sessionId = trim(strval(
									$responseObj->id
								));
							} else {
								$sessionId = null;
							}

							if ($sessionId) {
								$ci =& get_instance();

								// Create a place holder for our recording
								$audioFile = new VBX_Audio_File();
								$audioFile->label = "Recording with " . format_phone($to);
								$audioFile->user_id = intval($this->session->userdata('user_id'));
								$audioFile->recording_call_sid = "$sessionId";
								$audioFile->tag = $this->input->post('tag');
								$audioFile->save();

								$json['id'] = $audioFile->id;
								$json['message'] = '';
								$json['error'] = false;
							}
						}
					} catch (Exception $e) {
						// error
					}
				}
			}
		}

		$data = array();
		$data['json'] = $json;
		$this->response_type = 'json';
		$this->respond('', null, $data);
	}

	function prompt_for_recording_twiml()
	{
		$this->request = new TwilioUtils($this->twilio_sid, $this->twilio_token);
		$this->response = new Response();

		$audioFile = VBX_Audio_File::get(array('recording_call_sid' => $this->request->CallSid));

		if (!$audioFile->cancelled)
		{
			$this->response->addSay("Re-chord your message after the beep, press the pound key when finished.");
			$this->response->addRecord(array('action' => site_url('audiofiles/replay_recording_twiml')));

			$this->response->addSay("We didn't get a recording from you, try again.");
			$this->response->addRedirect(site_url('audiofiles/prompt_for_recording_twiml'));

		}
		else
		{
			$this->response->addSay("The recording was cancelled.");
			$this->response->addHangup();
		}

		return $this->response->Respond();
	}

	function replay_recording_twiml()
	{
		$this->request = new TwilioUtils($this->twilio_sid, $this->twilio_token);
		$this->response = new Response();

		if ($this->request->RecordingUrl)
		{
			// Stuff this in our session.  We'll come get it later when it's time to save!
			$recording = $this->request->RecordingUrl . '.mp3';
			$this->session->set_userdata('current-recording', $recording);
		}

		$this->response->addPause(array('length' => 1));
		$this->response->addSay('Recorded the following: ');
		$gather = $this->response->addGather(array('numDigits' => 1,
												   'method' => 'POST',
												   'action' => site_url('audiofiles/accept_or_reject_recording_twiml')));
		$gather->addPlay($this->session->userdata('current-recording'));
		$gather->addSay('If you like this message, press 1.	 To record a different message, press 2.');

		// If they don't enter anything at the prompt, do the replay again.
		$this->response->addRedirect(site_url('audiofiles/replay_recording_twiml'));

		return $this->response->Respond();
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
		$this->request = new TwilioUtils($this->twilio_sid, $this->twilio_token);
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
				$twilio = new TwilioRestClient($this->twilio_sid,
											   $this->twilio_token,
											   $this->twilio_endpoint);

				error_log("Redirecting to cancel page!");
				$response = $twilio->request("Accounts/{$this->twilio_sid}/Calls/" . $audioFile->recording_call_sid,
											 'POST',
											 array("CurrentUrl" => site_url('audiofiles/hangup_on_cancel'))
											 );

				$audioFile->cancelled = true;
				$audioFile->save();
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
