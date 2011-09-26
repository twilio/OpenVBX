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
require_once(APPPATH.'libraries/tropo/tropo.class.php');

class Web extends MY_Controller {

	protected $response;
	protected $request;

	private $data = array();
	protected $user_id;

	private $tropo;
	private $tropo_session;

	private $twilio_request;
	private $twilio_response;
	
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

		$this->request = new TwilioUtils($this->twilio_sid, $this->twilio_token);
		$this->response = new Response();
	}

	public function index()
	{
		redirect('');
	}

	public function setup()
	{
		$json = array( 'error' => false, 'message' => 'VoiceVault call initiated successfully.' );

		if (!$this->user_id) {
			$json['error'] = true;
			$json['message'] = "Invalid access. User must be logged in.";
		}

		if (!$json['error']) {
			try {
				$this->initiate_call('voicevault_enroll');
			} catch (Exception $e) {
				$json['error'] = true;
				$json['message'] = $e->getMessage();
			}
		}

		echo json_encode($json);
	}

	private function initiate_call($method)
	{
		// Get the user's primary device
		$devices = $this->vbx_device->get_by_user($this->user_id);
		$toDial = null;
		foreach ($devices as $device) {
			if ($device->is_active && $device->name <> 'Phono') {
				$toDial = $device;
				break;
			}
		}

		// Look for a valid number & token (tropo)
		$this->load->model('vbx_incoming_numbers');
		$this->load->model('vbx_settings');
		$numbers = $this->vbx_incoming_numbers->get_numbers();
		$numberMatch = null;
		$user_id = $this->user_id;
		$user = VBX_User::get(array('id'=>$user_id));
		$voicevaultNumber = $this->vbx_settings->get('voicevault_number', $user->tenant_id);
		foreach ($numbers as $number) {
			// Check url to make sure the tropo app can be accessed
			$baseUrl = base_url();
			$baseUrl = str_replace(
				array('www.', 'http://', 'https://'),
				'', 
				$baseUrl
			);
			// Check the number match
			// TODO: Fix twilio numbers
			if (($number->voice_token 
					&& strpos($number->url, $baseUrl) !== false 
					&& strpos($number->url, 'tropo/start/voice/0') === false)
				// || $number->api_type == 'twilio'
			)
			{
				if (!$voicevaultNumber ||
					$voicevaultNumber == $number->raw_phone)
				{
					$numberMatch = $number;
					break;
				}
			}
		}

		$from = PhoneNumber::normalizePhoneNumberToE164(
			$numberMatch->raw_phone);
		$to = PhoneNumber::normalizePhoneNumberToE164(
			$toDial->value);

		// Now we have the number, do something with it
		switch ($numberMatch->api_type) {
			case 'twilio':
				$twilio = new TwilioRestClient($this->twilio_sid,
											   $this->twilio_token,
											   $this->twilio_endpoint);
				
				// TODO: add voicevault twiml
				$method = substr($method, strpos($method, '_')+1);
				$recording_url = site_url("voicevault/twiml/$method").'?'.http_build_query(compact('to', 'from', 'user_id'));

				$response = $twilio->request("Accounts/{$this->twilio_sid}/Calls",
											'POST',
											array( "Caller" => $from,
													"Called" => $to,
													"Url" => $recording_url
													)
											 );
				break;
			case 'tropo':
		        $ch = curl_init("http://api.tropo.com/1.0/sessions?action=create&token={$numberMatch->voice_token}&to=".urlencode($to)."&from=".urlencode($from)."&user_id=$user_id&type=$method");
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		        $result = curl_exec($ch);
		        curl_close($ch);
				break;
			
			default:
				throw new Exception("Invalid number.");
				break;
		}
	}

	public function reset($email)
	{
		$user = VBX_User::get(array('email'=>$email));

		if (!$user) {
			$this->session->set_flashdata('error', "No active account found.");
			return redirect('auth/voicevault');
		}

		// Get voice vault settings
		$userData = unserialize($user->data);
		$claimantID = $userData['claimant_id'];
		$this->user_id = $user->id;

		$this->session->set_userdata('user_id', $this->user_id);

		if ($claimantID) {
			// Reset password
			$this->initiate_call('voicevault_reset');
		} else {
			$this->session->set_flashdata('error', "VoiceVault has not been setup for this account.");
			return redirect('auth/reset');
		}

		$this->session->set_flashdata('error', "VoiceVault call initiated successfully.");
		return redirect('auth/voicevault');
	}
	
}
