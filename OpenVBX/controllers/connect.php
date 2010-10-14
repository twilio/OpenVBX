<?php
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
	
class Connect extends User_Controller
{
	private $new_number = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('applet');
		$this->section = 'flows';
		$this->admin_only($this->section);
	}


	public function index()
	{
		$data = $this->init_view_data();
		$flows = VBX_Flow::search();
   
		$numbers = array();
		try
		{
			$numbers = $this->vbx_incoming_numbers->get_numbers();
		}
		catch (VBX_IncomingNumberException $e)
		{
			$this->error_message = ErrorMessages::message('twilio_api', $e->getCode());
		}

		$incoming_numbers = array();
		// now generate table
		if(count($numbers) > 0)
		{
			$flows = VBX_Flow::search();
			foreach($numbers as $item)
			{
				$item_msg = '';
				if(is_object($this->new_number) &&
				   $this->new_number->id == $item->id)
				{
					$item_msg = 'New';
				}

				$flow_name = '(Not Set)';
				foreach($flows as $flow)
				{
					if($flow->id == $item->flow_id)
					{
						$flow_name = '';
					}
				}
				
				$incoming_numbers[] = array(
											'id' => $item->id,
											'name' => $item->name,
											'trial' => (isset($item->trial) && $item->trial == 1)? 1 : 0,
											'phone' => format_phone($item->phone),
											'status' => $item_msg,
											'flow_id' => $item->flow_id,
											'flow_name' => $flow_name,
											'flows' => $flows,
											);
			}

		}
		$data['numbers'] = $incoming_numbers;
		$data['flows'] = $flows;
		$data['twilio_sid'] = $this->twilio_sid;
		
		if(empty($this->error_message))
		{
			$error_message = $this->session->flashdata('error');
			if(!empty($error_message))
			{
				$this->error_message = $this->session->flashdata('error');
			}
		}

		if(!empty($this->error_message))
		{
			$data['error'] = CI_Template::literal($this->error_message);
		}

		$data['counts'] = $this->message_counts();

		
		return $this->respond('', 'connect', $data);
	}
}