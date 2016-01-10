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

require_once(APPPATH.'libraries/twilio.php'); // @deprecated in 1.1

class TwimlException extends Exception {}

/**
 * This controller handles incomming calls from Twilio and outputs response
 * @property VBX_Message $vbx_message
 */
class Twiml extends MY_Controller {

	protected $response;

	private $flow;
	private $flow_id;
	private $flow_type = 'voice';
	
	protected $say_params;
	
	// This is an API response controller, suppress warnings & notices
	// to avoid breakage in operation
	protected $suppress_warnings_notices = true;

	public function __construct()
	{
		// this is an API controller, suppress warning & notice output to avoid XML breakage
		ini_set('display_errors', 'Off');
		
		parent::__construct();

		$this->load->helper('cookie');

		$this->load->library('applet');
		$this->load->library('TwimlResponse');

		$this->load->model('vbx_flow');
		$this->load->model('vbx_rest_access');
		$this->load->model('vbx_user');
		$this->load->model('vbx_message');

		$this->say_params = array(
			'voice' => $this->vbx_settings->get('voice', $this->tenant->id),
			'language' => $this->vbx_settings->get('voice_language', $this->tenant->id)
		);

		$this->flow_id = get_cookie('flow_id');
		$this->response = new TwimlResponse;
	}

	function index()
	{
		redirect('');
	}

	function start_sms($flow_id)
	{
		validate_rest_request();

		log_message("info", "Calling SMS Flow $flow_id");
		$body = $this->input->get_post('Body');
		$this->flow_type = 'sms';

		$this->session->set_userdata('sms-body', $body);

		$flow_id = $this->set_flow_id($flow_id);
		$flow = $this->get_flow();
		$flow_data = array();
		if(is_object($flow) && strlen($flow->sms_data))
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
			$this->response->say('Error 4-oh-4 - Flow not found.', $this->say_params);
			$this->response->respond();
		}
	}

	function start_voice($flow_id)
	{
		validate_rest_request();
		
		log_message("info", "Calling Voice Flow $flow_id");
		$this->flow_type = 'voice';

		$flow_id = $this->set_flow_id($flow_id);
		$flow = $this->get_flow();
		$flow_data = array();
		if(is_object($flow) && strlen($flow->data))
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
			$this->response->say('Error 4-oh-4 - Flow not found.', $this->say_params);
			$this->response->respond();
		}
	}

	public function sms($flow_id, $inst_id)
	{
		$this->flow_type = 'sms';
		$redirect = $this->session->userdata('redirect');
		if(!empty($redirect))
		{
			$this->response->redirect($redirect);
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
						/** @var stdClass $flow_data */
						$flow_data = get_object_vars(json_decode($sms_data));
						/** @var stdClass $instance */
						$instance = isset($flow_data[$inst_id])? $flow_data[$inst_id] : null;
					}

					if(!is_null($instance))
					{
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
						/** @var stdClass $flow_data */
						$flow_data = get_object_vars(json_decode($voice_data));
						/** @var stdClass $instance */
						$instance = isset($flow_data[$inst_id])? $flow_data[$inst_id] : null;
					}

					if(!is_null($instance))
					{
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
				$this->response->say('Unknown applet instance in flow '.$flow_id, $this->say_params);
				$this->response->respond();
			}

		}
		catch(Exception $ex)
		{
			$this->response->say('Error: ' . $ex->getMessage(), $this->say_params);
			$this->response->respond();
		}
	}

	function whisper()
	{
		$name =	$this->input->get_post('name');
		if(empty($name))
		{
			$name = "Open VeeBee Ex";
		}

		/* If we've received any input */
		$digits = clean_digits($this->input->get_post('Digits'));
		if(strlen($digits) > 0) {
			if($digits != '1') {
				$this->response->hangup();
			}
		} else {
			/* Prompt the user to answer the call */
			$gather = $this->response->gather(array('numDigits' => '1'));
			$say_number = implode(' ', str_split($this->input->get_post('From')));
			$gather->say("This is a call for {$name}. To accept, Press 1.", $this->say_params);
			$this->response->hangup();
		}

		$this->response->respond();
	}

	function redirect($path, $singlepass = false)
	{	
		if(!$this->session->userdata('loggedin')
		   && !$this->login_call($singlepass))
		{
			$this->response->say("Unable to authenticate this call.	Goodbye", $this->say_params);
			$this->response->hangup();
			$this->response->respond();
			return;
		}

		$path = str_replace('!', '/', $path);
		$this->response->redirect(site_url($path), array('method' => 'POST'));
		$this->response->respond();
	}

	/**
	 * Dial
	 * 
	 * Callback method that responds to a Twilio request and provides
	 * a number for Twilio to dial.
	 * 
	 * Overloaded by Twilio Client integration - Twilio Client connection
	 * requests automatically include the "1" Digit to immediately connect
	 * the call
	 *
	 * @return void
	 */
	public function dial()
	{
		validate_rest_request();

		$rest_access = $this->input->get_post('rest_access');
		$to = $this->input->get_post('to');
		$callerid = $this->input->get_post('callerid');
		$record = $this->input->get_post('record');

		if(!$this->session->userdata('loggedin')
		   && !$this->login_call($rest_access))
		{
			$this->response->say("Unable to authenticate this call.	Goodbye", $this->say_params);
			$this->response->hangup();
			$this->response->respond();
			return;
		}
		
		// Response
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

		$digits = clean_digits($this->input->get_post('Digits'));
		if($digits !== false && $digits == 1) 
		{
			$options = array(
				'action' => site_url("twiml/dial_status").'?'.http_build_query(compact('to')),
				'callerId' => $callerid,
				'timeout' => $this->vbx_settings->get('dial_timeout', $this->tenant->id)
			);
			
			if($record !== false)
			{
				$options['record'] = $record;
			}
			
			if (filter_var($this->input->get_post('to'), FILTER_VALIDATE_EMAIL)) 
			{
				$this->dial_user_by_email($this->input->get_post('to'), $options);
			}
			elseif(preg_match('|client:[0-9]{1,4}|', $this->input->get_post('to')))
			{
				$this->dial_user_by_client_id($this->input->get_post('to'), $options);
			}
			else 
			{
				$to = normalize_phone_to_E164($to);
				$this->response->dial($to, $options);
			}
		} 
		else 
		{
			$gather = $this->response->gather(array('numDigits' => 1));
			$gather->say("Hello {$name}, this is a call from VeeBee Ex, to accept, press 1.", 
						$this->say_params);
		}

		$this->response->respond();
	}
	
	/**
	 * Dial a user by 'client:1' format
	 *
	 * @todo not implemented
	 * @param string $client_id 
	 * @param array $options
	 * @return void
	 */
	protected function dial_user_by_client_id($client_id, $options)
	{
		$user_id = intval(str_replace('client:', '', $client_id));
		
		$user = VBX_User::get(array('id' => $user_id));
		if ($user instanceof VBX_User)
		{		
			$dial = $this->response->dial(NULL, $options);
			$dial->client($user_id);
		}
		else
		{
			$this->reponse->say('Unknown client id: '.$user_id.'. Goodbye.');
			$this->response->hangup();
		}
	}
	
	/**
	 * Dial a user identified by their email address
	 *
	 * Uses $user->setting('online') to determine if user "wants" to be contacted via
	 * Twilio Client. Passed in "online" status via $_POST can override the
	 * attempt to dial Twilio Client even if the person has set their status
	 * to online. The $_POST var should be representative of the Presence 
	 * Status of the user being dialed (if known).
	 * 
	 * @param string $user_email 
	 * @param array $options 
	 * @return void
	 */
	protected function dial_user_by_email($user_email, $options) {
		$user = VBX_User::get(array(
			'email' => $user_email
		));
		
		if ($user instanceof VBX_User)
		{
			$dial_client = ($user->setting('online') == 1);
			
			/**
			 * Only override the user status if we've been given
			 * an explicit opinion on the user's online status
			 */
			$client_status = $this->input->get_post('online');
			if (!empty($client_status) && $client_status == 'offline') 
			{
				$dial_client = false;
			}

			if (count($user->devices))
			{
				$options['sequential'] = 'true';
				$dial = $this->response->dial(NULL, $options);
			
				foreach ($user->devices as $device) 
				{
					if ($device->is_active)
					{
						if (strpos($device->value, 'client:') !== false && $dial_client)
						{
							if ($dial_client) 
							{
								$dial->client($user->id);
							}
						}
						else {
							$dial->number($device->value);
						}
					}
				}
			}
			else 
			{
				$this->response->say("We're sorry, this user is currently not reachable.".
									" Goodbye.");
			}
		}
		else
		{
			$this->response->say("We're sorry, that user doesn't exist in our system.".
								" Please contact your system administrator. Goodbye.");
		}		
	}

	function dial_status()
	{
		if($this->input->get_post('DialCallStatus') == 'failed')
		{
			$this->response->say('The number you have dialed is invalid. Goodbye.', 
								$this->say_params);
		}
		$this->response->hangup();
		$this->response->respond();
	}

	function transcribe()
	{
		// attatch transcription to the recording
		$notify = TRUE;
		$this->load->model('vbx_message');
		try
		{
			$call_sid = $this->input->get_post('CallSid');
			if(empty($call_sid))
			{
				throw new TwimlException('CallSid empty: possible non-twilio client access');
			}

			try
			{
				$message = $this->vbx_message->get_message(array(
													'call_sid' => $this->input->get_post('CallSid')
												));

				$message->content_text = $this->input->get_post('TranscriptionText');
				$this->vbx_message->save($message, $notify);
			}
			catch(VBX_MessageException $e)
			{
				throw new TwimlException($e->getMessage());
			}
		}
		catch(TwimlException $e)
		{
			log_message('error', 'Could not transcribe message: '.$e->getMessage());
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
				return true;
			}
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

	/**
	 * fetch the current flow and set up shared objects if necessary
	 * @param int $flow_id
	 * @return VBX_Flow
	 */
	private function get_flow($flow_id = 0)
	{
		if($flow_id < 1) 
		{
			$flow_id = $this->flow_id;
		}
		
		if(is_null($this->flow)) 
		{
			$this->flow = VBX_Flow::get(array( 'id' => $flow_id, 'numbers' => false));
		}
		
		if($flow_id > 0)
		{
			if(!empty($this->flow))
			{
				if( $this->flow_type == 'sms' )
				{
					// make flow data visible to all applets
					Applet::$flow_data = $this->flow->sms_data;	
				}
				else
				{
					// make flow data visible to all applets
					Applet::$flow_data = $this->flow->data;
				}
			}
		}

		return $this->flow;
	}

}
