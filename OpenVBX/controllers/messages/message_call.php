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
			$to = $this->input->post('to');
			$callerid = $this->input->post('callerid');
			$from = $this->input->post('from');
			
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
			
			$rest_access = $this->make_rest_access();
			$this->vbx_call->make_call($from, $to, $callerid, $rest_access);
			if($message_id)
			{
				$annotation_id = $this->vbx_message->annotate($message_id,
															  $this->user_id,
															  'Called back from voicemail',
															  'called');
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
}