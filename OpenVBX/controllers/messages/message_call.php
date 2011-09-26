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

class Message_CallException extends Exception {}

class Message_Call extends User_Controller
{
	function __construct()
	{
		parent::__construct();
	}
	
	function index($message_id = false)
	{

		try
		{
			$to = normalize_phone_to_E164($this->input->post('to'));
			$callerid = normalize_phone_to_E164($this->input->post('callerid'));
			$from = normalize_phone_to_E164($this->input->post('from'));

			// First check if this is a twilio or tropo number
			$this->load->model('vbx_incoming_numbers');
			$numbers = $this->vbx_incoming_numbers->get_numbers();

            $numberMatch = null;
            foreach ($numbers as $numberObj) {
                if ($numberObj->raw_phone == $callerid) {
                    $numberMatch = $numberObj;
                    break;
                }
            }
			
			$this->load->model('vbx_call');
			$json['error'] = false;
			$json['message'] = '';
			if(empty($from))
			{
				$this->load->model('vbx_device');
				$devices = $this->vbx_device->get_by_user($this->user_id);
				if(!empty($devices[0]))
				{
					$from = $devices[0]->value;
				}
				
				if(empty($from))
				{
					throw new Message_CallException('You may not have any devices setup under this user account.  Please see the devices section more information.');
				}
			}
			
			if ($numberMatch->api_type == 'twilio') {
				$rest_access = $this->make_rest_access();
				$this->vbx_call->make_call($from, $to, $callerid, $rest_access);
				if($message_id)
				{
					$annotation_id = $this->vbx_message->annotate($message_id,
																  $this->user_id,
																  'Called back from voicemail',
																  'called');
				}
			} else {
				// $rest_access = $this->make_rest_access();
				$this->vbx_call->make_call_tropo($from, $to, $callerid, $numberMatch->voice_token);
				if($message_id)
				{
					$annotation_id = $this->vbx_message->annotate($message_id,
																  $this->user_id,
																  'Called back from voicemail',
																  'called');
				}
			}
			
		}
		catch(Exception $e)
		{
			$json['message'] = $e->getMessage();
			$json['error'] = true;
		}

		$data['json'] = $json;

		if($this->response_type == 'html')
		{
			redirect('messages/inbox');
		}
		
		$this->respond('', 'message_call', $data);
	}

	/**
	 * Display the popup for making calls with Twilio Client
	 *
	 * @return void
	 */
	public function client() 
	{	
		$caller_id = $this->input->get('callerid');
		if (empty($caller_id)) {
			// grab user's default device
			$user = VBX_User::get($this->session->userdata['user_id']);
			$devices = $this->vbx_device->get_by_user($this->user_id);
			if (!empty($devices)) {
				$caller_id = $devices[0]->value;
			}
		}
		
		$client_params = array(
			'callerid' => normalize_phone_to_E164($caller_id),
			'application_id' => 'client'
		);
		
		if ($this->input->get('outgoing')) 
		{
			// functionality for using the "dial" modal to initiate a call
			$to = normalize_phone_to_E164($this->input->get('to'));
			if (empty($to)) {
				$to = htmlspecialchars($this->input->get('to'));
			}
			$client_params = array_merge($client_params, array(
				'to' => $to, // if this is empty then the window will not auto dial
			));
		}
		elseif ($this->input->get('incoming')) {
			$client_params = array_merge($client_params, array(
				'incoming' => true
			));
		}
		
		$client_data = array(
			'client_params' => json_encode($client_params)
		);
		$javascript = $this->load->view('client_js', $client_data, true);
		$this->template->add_js($javascript, 'embed', true);
		
		$data = array(
			'title' => 'dialer',
			'client_params' => $client_params
		);
		$this->respond('Dialer', 'call', $data, '', 'layout/dialer');
	}
}
