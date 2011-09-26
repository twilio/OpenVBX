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

class Message_TextException extends Exception {}

class Message_Text extends User_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('vbx_incoming_numbers');
		$this->load->model('vbx_sms_message');
	}
	
	function index($message_id = false)
	{
		try
		{
			$content = substr($this->input->post('content'), 0, 160);
			$to = $this->input->post('to');
			$from = $this->input->post('from');
			$numbers = array();
			
			if(empty($from))
			{
				
				try
				{
					$numbers = $this->vbx_incoming_numbers->get_numbers();
					if(empty($numbers))
					{
						throw new Message_TextException("No SMS Enabled numbers");
					}
					$from = $numbers[0]->phone;
				}
				catch(VBX_IncomingNumberException $e)
				{
					throw new Message_TextException("Unable to retrieve numbers: ".$e->getMessage());
				}
			}

			if(empty($from))
			{
				$this->load->model('device');
				$devices = $this->device->get_by_user($this->user_id);
				if(!empty($devices[0]))
				{
					$from = $devices[0]->value;
				}
			}
		
			$rest_access = $this->make_rest_access();
			
			$json['error'] = false;
			$json['message'] = '';
			
			try
			{
				$this->vbx_sms_message->send_message($from, $to, $content);
			}
			catch(VBX_Sms_MessageException $e)
			{
				if (strpos($e->getMessage(), 'From phone number provided is not a valid')
				|| strpos($e->getMessage(), 'resource was not found') !== false) {
					// Try with tropo
					$this->vbx_sms_message->send_message($from, $to, $content, 
						'tropo');
				} else {
					throw new Message_TextException($e->getMessage());
				}
			}

			if($message_id)
			{
				error_log("SMS Message ID: $message_id");
				$annotation_id = $this->vbx_message->annotate($message_id,
															  $this->user_id,
															  "$from to ".format_phone($to).": $content",
															  'sms');
			}
			
		}
		catch(Message_TextException $e)
		{
			$json['message'] = $e->getMessage();
			$json['error'] = true;
		}
		$data['json'] = $json;

		if($this->response_type == 'html')
		{
			redirect('messages/inbox');
		}
		
		$this->respond('', 'message_sms', $data);
	}

}