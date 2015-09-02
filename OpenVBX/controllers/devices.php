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

class Devices extends User_Controller {

	protected $response;
	protected $request;

	private $data = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->model('vbx_device');
		$this->template->write('title', 'Devices');
		$this->section = 'devices';
	}

	public function index()
	{
		$this->template->add_js('assets/j/account.js');
		$this->template->add_js('assets/j/devices.js');

		$data = $this->init_view_data();
		$user = VBX_user::get(array('id' => $this->user_id));
		
		$data['user'] = $user;
		$data['numbers'] = $data['devices'] = $user->devices;
		$data['show_iphone_app'] = $this->config->item('show_iphone_app') ? true : false;

		return $this->respond('', 'devices', $data);
	}

	public function edit()
	{
		$is_admin = $this->session->userdata('is_admin');
		$user = new VBX_User();
		$params = array();
		foreach($user->fields as $field) {
			$val = $this->input->post($field);
			/* Disallow people from changing certain settings */
			if(in_array($field, $user->admin_fields))
			{
				if($val && $is_admin) $params[$field] = $val;
			}
			else
			{
				if($val) $params[$field] = $val;
			}

			// The value for some fields should also be saved to the session
			if ($field === 'email')
			{
				$this->session->set_userdata('email', trim($val));
			}
		}

		if($user->update($this->user_id, $params)) {
			$this->session->set_flashdata('message_edit', 'User data changed');
			redirect('account');
		} else {
			$this->data['error_edit'] = '';
			$this->index();
		}
	}

	public function number($key = 0)
	{
		switch($key)
		{
			case 'order':
				return $this->order_handler();
			default:
				return $this->number_handler($key);
		}
	}

	public function send_iphone_guide() {
		$user = VBX_user::get(array('id' => $this->user_id));
		$this->data = array('error' => false, 'message' => 'OK');


		openvbx_mail($user->email,
					 "iPhone installation Guide",
					 'iphone-guide',
					 array('email' => $user->email));


		echo json_encode($this->data);
	}

	private function number_handler($id)
	{
		switch($this->request_method) {
			case 'POST':
				if(!empty($id) && intval($id) > 0)
				{
					return $this->update_number($id);
				}
				return $this->add_number();
			case 'DELETE':
				return $this->delete_number($id);
		}
	}

	private function order_handler()
	{
		switch($this->request_method) {
			case 'POST':
				return $this->update_order();
		}
	}

	private function update_number($device_id)
	{
		$data['json'] = array('error' => false,
							  'message' => '');

		$number = $this->input->post('device');
		$device = VBX_Device::get($device_id);
		if(isset($number['value']))
		{
			$device->value = normalize_phone_to_E164($number['value']);
		}

		if(isset($number['sms']))
		{
			$device->sms = intval($number['sms']) == 0? 0 : 1;
		}

		if(isset($number['is_active']))
		{
			$device->is_active = intval($number['is_active']) == 0? 0 : 1;
		}

		try
		{
			$device->save();
		}
		catch(VBX_DeviceException $e)
		{
			error_log($e->getMessage());
			$device['json']['error'] = true;
			$device['json']['message'] = 'Unable to update device settings';
		}

		if($this->response_type == 'html')
		{
			redirect('account#devices');
		}

		$this->respond('', 'account/number', $data);
	}

	private function update_order()
	{
		$data['json'] = array('error' => false, 'message' => '');

		$order = $this->input->post('order');
		try
		{
			foreach($order as $sequence => $device_id)
			{
				$params = array('sequence' => $sequence);
				$device = VBX_Device::get(array('id' => $device_id,
												'user_id' => $this->user_id));
				if(!$device) {
					error_log('Device no longer exists: '.$device_id);
					continue;
				}
				$device->sequence = $sequence;
				$device->save();
			}

		}
		catch(VBX_DeviceException $e)
		{
			$data['json']['error'] = true;
			$data['json']['message'] = 'One or more device sequences were not updated';
		}

		if($this->response_type == 'html')
		{
			redirect('account/number');
		}

		$this->respond('', 'account/number', $data);
	}

	private function add_number()
	{
		$number = array();
		$number = $this->input->post('number');
		$number['value'] = normalize_phone_to_E164($number['value']);
		$number['user_id'] = $this->user_id;
		// sms is always enabled by default
		$number['sms'] = 1;
		try
		{
			if(empty($number['value']) ||
			   empty($number['name']))
			{
				$message = 'All fields required';
				throw new VBX_DeviceException($message);
			}

			$number_id = $this->vbx_device->add($number);
			$response = array('error' => false,
							  'message' => '',
							  'id' => $number_id,
							  'name' => htmlspecialchars($number['name']),
							  'value' => format_phone($number['value']),
							  'sms' => $number['sms'],
							  );
		}
		catch(VBX_DeviceException $e)
		{
			$response = array('error' => true,
							  'message' => $e->getMessage(),
							  );
		}

		$data['json'] = $response;

		if($this->response_type == 'html')
		{
			redirect('account');
		}

		return $this->respond('', 'account', $data);
	}

	private function delete_number($id)
	{
		$number = $this->vbx_device->get($id);
		$response = array('error' => false,
						  'message' => '',);

		if($number && $number->user_id == $this->user_id)
		{
			try
			{
				$number->delete();
			}
			catch(VBX_DeviceException $e)
			{
				error_log($e->getMessage());
				$response = array('error' => true,
								  'message' => 'Unable to delete.  Please contact support.',);
			}
		}
		else
		{
			$response = array('error' => true,
							  'message' => 'Permission Denied');
		}

		if($this->response_type == 'html')
		{
			echo 'test';exit;
		}

		$data['json'] = $response;
		return $this->respond('', 'account', $data);
	}
	
	/**
	 * Refresh the user's devices list in the dialer
	 *
	 * @return json
	 */
	public function refresh_dialer() {
		$user = VBX_User::get(array('id' => $this->session->userdata('user_id')));
		$browserphone = array(
			'call_using_options' => array()
		);
		if (count($user->devices)) 
		{
			foreach ($user->devices as $device)
			{
				if (strpos($device->value, 'client:') !== false)
				{
					continue;
				}
				$browserphone['call_using_options']['device:'.$device->id] = array(
					'title' => 'Device: '.$device->name,
					'data' => (object) array(
						'number' => format_phone($device->value),
						'name' => $device->name
					)
				);
			}
		}
		
		$data = array(
			'browserphone' => $browserphone
		);

		$html = $this->load->view('dialer/devices', $data, true);
		
		$response['json'] = array(
			'error' => false,
			'html' => $html
		);
		$this->respond('', 'dialer/devices', $response);
	}
}
