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
require_once(APPPATH.'libraries/Applet.php');

class TwimlException extends Exception {}

/* This controller handles incomming calls from Twilio and outputs response
*/
class Twiml extends MY_Controller {

	protected $response;
	protected $request;

	private $flow;
	private $flow_id;
	private $flow_type = 'voice';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('cookie');
		$this->request = new TwilioUtils($this->twilio_sid, $this->twilio_token);
		$this->response = new Response();
		$this->flow_id = get_cookie('flow_id');
		$this->load->model('vbx_flow');
		$this->load->model('vbx_rest_access');
		$this->load->model('vbx_user');
		$this->load->model('vbx_message');
	}

	function index()
	{
		redirect('');
	}

	function start_sms($flow_id)
	{
		log_message("info", "Calling SMS Flow $flow_id");
		$body = $this->input->get_post('Body');
		$this->flow_type = 'sms';

		$this->session->set_userdata('sms-body', $body);

		$flow_id = $this->set_flow_id($flow_id);
		$flow = $this->get_flow();
		$flow_data = array();
		if(is_object($flow))
		{
			$flow_data = get_object_vars(json_decode($flow->sms_data));
		}

		$instance = isset($flow_data['start'])? $flow_data['start'] : null;
		if(is_object($instance))
		{
			$this->applet($flow_id, 'start', 'sms');
		}
		else
		{
			$this->response->addSay('Error 4 0 4 - Flow not found.');
			$this->response->Respond();
		}

	}

	function start_voice($flow_id)
	{
		log_message("info", "Calling Voice Flow $flow_id");
		$this->flow_type = 'voice';

		$flow_id = $this->set_flow_id($flow_id);
		$flow = $this->get_flow();
		$flow_data = array();
		if(is_object($flow))
		{
			$flow_data = get_object_vars(json_decode($flow->data));
		}

		$instance = isset($flow_data['start'])? $flow_data['start'] : null;

		if(is_object($instance))
		{
			$this->applet($flow_id, 'start');
		}
		else
		{
			$this->response->addSay('Error 4 0 4 - Flow not found.');
			$this->response->Respond();
		}
	}

	public function sms($flow_id, $inst_id)
	{
		$this->flow_type = 'sms';
		$redirect = $this->session->userdata('redirect');
		if(!empty($redirect))
		{
			$this->response->addRedirect($redirect);
			$this->session->set_userdata('last-redirect', $redirect);
			$this->session->unset_userdata('redirect');
			return $this->response->respond();
		}
		return $this->applet($flow_id, $inst_id, 'sms');
	}

	public function voice($flow_id, $inst_id)
	{
		return $this->applet($flow_id, $inst_id, 'voice');
	}

	private function applet_headers($applet, $plugin_dir_name)
	{
		$plugin = Plugin::get($plugin_dir_name);
		$plugin_info = ($plugin)? $plugin->getInfo() : false;

		header("X-OpenVBX-Applet-Version: {$applet->version}");
		if($plugin_info)
		{
			header("X-OpenVBX-Plugin: {$plugin_info['name']}");
			header("X-OpenVBX-Plugin-Version: {$plugin_info['version']}");
		}
		header("X-OpenVBX-Applet: {$applet->name}");
	}

	private function applet($flow_id, $inst_id, $type = 'voice')
	{
		$flow_id = $this->set_flow_id($flow_id);
		$flow = $this->get_flow();
		$instance = null;
		$applet = null;

		try
		{
			switch($type)
			{
				case 'sms':
					if(isset($_REQUEST['Body']) && $inst_id == 'start')
					{
						$_COOKIE['sms-body'] = $_REQUEST['Body'];
						$sms = $_REQUEST['Body'];

						// Expires after three hours
						set_cookie('sms-body', $sms, 60*60*3);
					}
					else
					{
						$sms = isset($_COOKIE['sms-body'])? $_COOKIE['sms-body'] : null;
						set_cookie('sms-body', null, time()-3600);
					}
					$sms_data = $flow->sms_data;
					if(!empty($sms_data))
					{
						$flow_data = get_object_vars(json_decode($sms_data));
						$instance = isset($flow_data[$inst_id])? $flow_data[$inst_id] : null;
					}

					if(!is_null($instance))
					{
						$plugin_dir_name = '';
						$applet_dir_name = '';
						list($plugin_dir_name, $applet_dir_name) = explode('---', $instance->type);

						$applet = Applet::get($plugin_dir_name,
											  $applet_dir_name,
											  null,
											  $instance);
						$applet->flow_type = $type;
						$applet->instance_id = $inst_id;
						$applet->sms = $sms;
						if($sms)
						{
							$_POST['Body'] = $_GET['Body'] = $_REQUEST['Body'] = $sms;
						}
						$this->session->unset_userdata('sms-body');

						$applet->currentURI = site_url("twiml/applet/sms/$flow_id/$inst_id");

						$baseURI = site_url("twiml/applet/sms/$flow_id/");
						$this->applet_headers($applet, $plugin_dir_name);
						echo $applet->twiml($flow, $baseURI, $instance);
					}
					break;
				case 'voice':
					$voice_data = $flow->data;
					if(!empty($voice_data))
					{
						$flow_data = get_object_vars(json_decode($voice_data));
						$instance = isset($flow_data[$inst_id])? $flow_data[$inst_id] : null;
					}

					if(!is_null($instance))
					{
						$plugin_dir_name = '';
						$applet_dir_name = '';
						list($plugin_dir_name, $applet_dir_name) = explode('---', $instance->type);

						$applet = Applet::get($plugin_dir_name,
											  $applet_dir_name,
											  null,
											  $instance);
						$applet->flow_type = $type;
						$applet->instance_id = $inst_id;
						$applet->currentURI = site_url("twiml/applet/voice/$flow_id/$inst_id");
						$baseURI = site_url("twiml/applet/voice/$flow_id/");
						$this->applet_headers($applet, $plugin_dir_name);

						echo $applet->twiml($flow, $baseURI, $instance);
					}
					break;
			}

			if(!is_object($applet))
			{
				$this->response->addSay("Unknown applet instance in flow $flow_id.");
				$this->response->Respond();
			}

		}
		catch(Exception $ex)
		{
			$this->response->addSay('Error: ' + $ex->getMessage());
		}

	}

	function whisper()
	{
		$name =	$this->input->get_post('name');
		if(empty($name))
		{
			$name = "Open V B X";
		}

		/* If we've received any input */
		if(strlen($this->request->Digits) > 0) {
			if($this->request->Digits != '1') {
				$this->response->addHangup();
			}
		} else {
			/* Prompt the user to answer the call */
			$gather = $this->response->addGather(array('numDigits' => '1'));
			$say_number = implode(' ', str_split($this->request->From));
			$gather->addSay("This is a call for {$name}. To accept, Press 1.");
			$this->response->addHangup();
		}

		$this->response->Respond();
	}

	function redirect($path, $singlepass = false)
	{
		if(!$this->session->userdata('loggedin')
		   && !$this->login_call($singlepass))
		{
			$this->response->addSay("Unable to authenticate this call.	Goodbye");
			$this->response->addHangup();
			$this->response->Respond();
			return;
		}

		$path = str_replace('!', '/', $path);
		$this->response->addRedirect(site_url($path), array('method' => 'POST'));
		$this->response->Respond();
	}

	function voicevault()
	{
		$from = $this->input->get('from');
		$to = $this->input->get('to');
		$user_id = $this->input->get('user_id');

		$this->session->set_userdata(array(
			'from'=>$from,
			'to'=>$to,
			'user_id'=>$user_id
		));

		$this->response->addRedirect(site_url('voicevault/twiml'));
		$this->response->Respond();
	}

	function dial()
	{
		$rest_access = $this->input->get_post('rest_access');
		$to = $this->input->get_post('to');
		$callerid = $this->input->get_post('callerid');

		if(!$this->session->userdata('loggedin')
		   && !$this->login_call($rest_access))
		{
			$this->response->addSay("Unable to authenticate this call.	Goodbye");
			$this->response->addHangup();
			$this->response->Respond();
			return;
		}
		/* Response */
		log_message('info', $rest_access. ':: Session for phone call: '.var_export($this->session->userdata('user_id'), true));
		$user = VBX_User::get($this->session->userdata('user_id'));
		$name = '';
		if(empty($user))
		{
			log_message('error', 'Unable to find user: '.$this->session->userdata('user_id'));
		}
		else
		{
			$name = $user->first_name;
		}

		if($this->request->Digits !== false
		   && $this->request->Digits == 1) {
			$options = array('action' => site_url("twiml/dial_status").'?'.http_build_query(compact('to')),
							 'callerId' => $callerid);

			$dial_client = false;
			$to = normalize_phone_to_E164($to);
			if (!is_numeric($to)) {
				//$to = htmlspecialchars($this->input->get_post('to'));
				// look up user by email address
				$user = VBX_User::get(array(
					'email' => $this->input->get_post('to')
				));
				if (!empty($user)) {
					$dial_client = true;
					$to = $user->id;
				}
			}
			
			if (!$dial_client) {
				$this->response->addDial($to, $options);
			}
			else {
				$dial = new Dial(NULL, $options);
				$dial->append(new Client($to));
				$this->response->append($dial);
			}

		} else {
			$gather = $this->response->addGather(array('numDigits' => 1));
			$gather->addSay("Hello {$name}, this is a call from v b x".
							", to accept, press 1.");
		}

		$this->response->Respond();
	}

	function dial_status()
	{
		if($this->request->DialCallStatus == 'failed')
		{
			$this->response
				->addSay('The number you have dialed is invalid. Goodbye.');
		}
		$this->response->addHangup();
		$this->response->Respond();
	}

	function transcribe()
	{
		error_log("transcribing: {$this->request->CallSid}");
		// attatch transcription to the recording
		$notify = TRUE;
		$this->load->model('vbx_message');
		try
		{
			if(empty($this->request->CallSid))
			{
				throw new TwimlException('CallSid empty: possible non-twilio client access');
			}

			try
			{
				$message = $this->vbx_message->get_message(array('call_sid' => $_REQUEST['CallSid']));

				$message->content_text = $this->request->TranscriptionText;
				$this->vbx_message->save($message, $notify);
			}
			catch(VBX_MessageException $e)
			{
				throw new TwimlException($e->getMessage());
			}
		}
		catch(TwimlException $e)
		{
			error_log($e->getMessage());
		}
	}

	/* Private utility functions here */
	private function login_call($singlepass)
	{
		/* Rest API Authentication - one time pass only */
		if(!empty($singlepass))
		{
			$ra = new VBX_Rest_access();
			$user_id = $ra->auth_key($singlepass);
			unset($_COOKIE['singlepass']);
			if($user_id)
			{
				$this->session->set_userdata('user_id', $user_id);
				$this->session->set_userdata('loggedin', true);
				$this->session->set_userdata('signature', VBX_User::signature($user_id));
			}

			return true;
		}

		return false;
	}

	private function set_flow_id($id)
	{
		$this->session->set_userdata('flow_id', $id);
		if($id != $this->flow_id AND $id > 0) {
			$this->get_flow($id);

			if(!empty($this->flow)) {
				$id = $this->flow->id;
				$this->flow_id = $id;
				set_cookie('flow_id', $id, 0);
			} else {
				$id = -1;
			}
		} else {
			$id = $this->flow_id;
		}
		return $id;
	}

	// fetch the current flow and set up shared objects if necessary
	private function get_flow($flow_id = 0)
	{
		if($flow_id < 1) $flow_id = $this->flow_id;
		if(is_null($this->flow)) $this->flow = VBX_Flow::get(array( 'id' => $flow_id, 'numbers' => false));

		if($flow_id > 0)
		{
			if(!empty($flow))
			{
				if( $this->flow_type == 'sms' )
				{
					Applet::$flow_data = $flow->sms_data;	// make flow data visible to all applets
				}
				else
				{
					Applet::$flow_data = $flow->data;	// make flow data visible to all applets
				}
			}
		}

		return $this->flow;
	}

}
