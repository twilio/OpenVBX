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

class Account extends User_Controller {

	protected $response;
	protected $request;

	private $data = array();
	private $notifications = array('message_edit',
								   'message_password',
								   'error_edit',
								   'error_password');
	
	private $editable_fields = array('first_name',
									 'last_name',
									 'email',
									 'is_admin',
									 'pin',
									 'notification');
	protected $user_id;
	
	public function __construct()
	{
		parent::__construct();
		$this->config->load('openvbx');
		$this->load->database();
		$this->load->model('vbx_device');
		$this->template->write('title', 'Account');

		$this->user_id = $this->session->userdata('user_id');
	}

	public function index()
	{
		$this->user($this->user_id);
	}

	public function user($user_id)
	{
		$this->template->add_js('assets/j/account.js');
		$this->template->add_js('assets/j/devices.js');
		$this->template->add_js('assets/j/messages.js');

		$data = $this->init_view_data();
		$user = VBX_user::get(array('id' => $user_id));
		$data['user'] = $user;
		
		foreach($this->notifications as $field)
		{
			$val = $this->session->flashdata($field);
			if(!empty($val)) $this->data[$field] = $val;
		}
		
		$numbers = $this->vbx_device->get_by_user($user_id);
		$data['numbers'] = $numbers;
		$data['devices'] = $this->vbx_device->get_by_user($user_id);
		
		$voicemail_value = $data['user']->voicemail;
		$data['voicemail_mode'] = '';
		$data['voicemail_play'] = '';
		$data['voicemail_say'] = '';

		if (!empty($voicemail_value))
		{
			if (preg_match('/^http/i', $voicemail_value) ||
				preg_match('/^vbx-audio-upload/i', $voicemail_value))
			{
				$data['voicemail_mode'] = 'play';
				$data['voicemail_play'] = $voicemail_value;
			}
			else
			{
				$data['voicemail_mode'] = 'say';
				$data['voicemail_say'] = $voicemail_value;
			}
		}

		if ($user_id == $this->session->userdata('user_id'))
		{
			$data['account_title'] = 'My Account';
			$data['content_menu_url'] = null;
		}
		else
		{
			$data['account_title'] = 'Edit Account: '.$user->full_name();
			$data['content_menu_url'] = site_url('accounts');
		}

		if ($err_msg = $this->session->flashdata('error_edit'))
		{
			$data['error_edit'] = $err_msg;
		}

		$data['current_user'] = VBX_User::get($this->session->userdata('user_id'));
		
		return $this->respond('', 'account', $data);
	}

	public function edit($user_id = null)
	{	
		// if no user-id passed, assume current user
		if (empty($user_id))
		{
			$user_id = $this->session->userdata('user_id');
		}
		
		$user_id = intval($user_id);
		$is_admin = $this->session->userdata('is_admin');

		if ($user_id != $this->session->userdata('user_id') && !$is_admin)
		{
			$this->session->set_flashdata('message_edit', 'You are not allowed to update'.
											' other users');
			redirect('/');
		}
		
		$user = VBX_User::get($user_id);
		foreach ($user->fields as $field)
		{
			$val = $this->input->post($field);
			if (in_array($field, $user->admin_fields))
			{
				if($user->id != $this->session->userdata('user_id') && $is_admin)
				{
					if (($val || $val === '0'))
					{
						$user->$field = $val;
					}
					else
					{
						$user->$field = '0';
					}
				}
			}
			else
			{
				if ($val || $val === '0')
				{
					$user->$field = $val;
				}
			}
		}
		
		if ($settings = $this->input->post('settings'))
		{
			foreach ($settings as $key => $value)
			{
				$user->setting_set($key, $value);
			}
		}
		
		try {
			$success = $user->save();
		}
		catch (Exception $e) {
			$error_message = $e->get_message();
		}
		
		if ($this->response_type == 'json')
		{
			$failmessage = 'an error occurred while updating the user';
			$successmessage = 'user status updated';
			
			$data = (isset($this->data) ? $this->data : array());
			$data['json'] = array(
				'error' => !$success,
				'message' => (!$success ? $failmessage : $successmessage)
			);
			$this->respond('', null, $data);
		}
		else
		{
			if (isset($success) && $success)
			{
				$this->session->set_flashdata('message_edit', 'User data updated');
			}
			else 
			{
				$message = 'Error updating user data';
				if (isset($error_message))
				{
					$message .= ': '.$error_message;
				}
				$this->session->set_flashdata('error_edit', $message);
			}
			
			if ($user_id == $this->session->userdata('user_id'))
			{
				$redirect_url = 'account';
			}
			else
			{
				$redirect_url = 'account/user/'.$user->id;
			}
			
			redirect($redirect_url);
		}
	}

	public function password($user_id)
	{
		$user_id = intval($user_id);
		$is_admin = $this->session->userdata('is_admin');

		if ($user_id != $this->session->userdata('user_id') && !$is_admin)
		{
			$this->session->set_flashdata('message_edit', 'You are not allowed to update'.
											' other users');
			redirect('/');
		}
		
		$user = VBX_user::get(array('id' => $user_id));

		$old_pw = $this->input->post('old_pw');
		$new_pw = $this->input->post('new_pw1');
		$new_pw2 = $this->input->post('new_pw2');
		$this->data['error'] = false;
		$message = '';

		if (VBX_User::authenticate($user, $old_pw))
		{
			try {
				$user->set_password($new_pw, $new_pw2);
				$message = 'Password Updated';
			}
			catch (Exception $e) {
				$this->data['error'] = true;
				$message = $e->getMessage();
			}
		}
		else
		{
			$this->data['error'] = true;
			$message = 'Incorrect Password';
		}
		
		if ($user_id == $this->session->userdata('user_id'))
		{
			$this->session->set_userdata('signature', VBX_User::signature($user_id));
		}
		
		$this->data['message'] = $message;

		echo json_encode($this->data);
	}
	
	public function rest_access_token()
	{
		try {
			$token = $this->make_rest_access();
			$data = array(
				'error' => false,
				'token' => $this->make_rest_access()
			);
		}
		catch (Exception $e) {
			$data = array(
				'error' => true,
				'message' => $e->getMessage()
			);
		}
		
		echo json_encode($data);
	}

	public function save_voicemail()
	{
		$data['json'] = array('error' => false, 'message' => '');

		$user = VBX_User::get($this->user_id);
		$user->voicemail = $this->input->post('voicemail');
		try
		{
			$user->save();
		}
		catch(VBX_UserException $e)
		{
			$data['json']['error'] = true;
			$data['json']['message'] = $e->getMessage();
		}
		return $data;
	}
	
	public function settings() 
	{
		$data['json'] = array(
			'error' => false,
			'message' => ''
		);

		if ($this->request_method == 'POST')
		{
			$settings = $this->input->post('settings');
			if (!empty($settings))
			{
				try {
					$user = VBX_User::get($this->session->userdata('user_id'));
					foreach ($settings as $key => $value)
					{
						$user->setting_set($key, $value);
					}
				}
				catch (Exception $e) {
					$data['json'] = array(
						'error' => true,
						'message' => $e->getMessage()
					);
				}
			}
		}
		else 
		{
			$data['json'] = array(
				'error' => true,
				'message' => 'Invalid request'
			);
		}

		$this->respond('', null, $data);
	}
	
	public function client_status() 
	{
		$data = array(
			'json' => array(
				'error' => true,
				'message' => 'Invalid Request'
			)
		);
		
		if ($this->input->post('clientstatus')) {
			$online = ($this->input->post('online') == 1);

			$user = VBX_User::get($this->session->userdata('user_id'));
			try {
				$user->setting_set('online', $online);
				
				$data['json'] = array(
					'error' => false,
					'message' => 'status updated',
					'client_status' => ($online ? 'online' : 'offline')
				);
			}
			catch (VBX_UserException $e) {
				$data['json'] = array(
					'error' => true,
					'message' => $e->getMessage()
				);
			}
		}
		
		$this->respond('', null, $data);
	}
}
