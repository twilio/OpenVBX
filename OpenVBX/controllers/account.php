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
		if (!$this->session->userdata('loggedin')) redirect('auth/login');

		$this->template->add_js('assets/j/account.js');
		$this->template->add_js('assets/j/devices.js');
		$this->template->add_js('assets/j/messages.js');

		$data = $this->init_view_data();
		$user = VBX_user::get(array('id' => $this->user_id));
		$data['user'] = $user;
		
		foreach($this->notifications as $field)
		{
			$val = $this->session->flashdata($field);
			if(!empty($val)) $this->data[$field] = $val;
		}
		
		$numbers = $this->vbx_device->get_by_user($this->user_id);
		$data['numbers'] = $numbers;
		$data['devices'] = $this->vbx_device->get_by_user($this->user_id);
		
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

		// Get voicevault claimant identifier
		$user = VBX_User::get(array('id'=>$this->user_id));
		$userData = unserialize($user->data);
		if ($userData && isset($userData['claimant_id']))
			$data['claimant_id'] = $userData['claimant_id'];
		else
			$data['claimant_id'] = null;

		return $this->respond('', 'account', $data);
	}

	public function edit()
	{
		if (!$this->session->userdata('loggedin')) redirect('auth/login');

		$is_admin = $this->session->userdata('is_admin');
		$user = new VBX_User();
		$params = array();
		foreach($user->fields as $field) {
			$val = $this->input->post($field);
			/* Disallow people from changing certain settings */
			if(in_array($field, $user->admin_fields))
			{
				if(($val || $val === '0') && $is_admin) $params[$field] = $val;
			}
			else
			{
				if($val || $val === '0') $params[$field] = $val;
			}

			// The value for some fields should also be saved to the session
			if ($field === 'email')
			{
				$this->session->set_userdata('email', trim($val));
			}
		}
		
		$success = $user->update($this->user_id, $params);

		// Update vbx settings
		$voicevault_id = $this->input->post('voicevault_id');
		$this->vbx_settings->set('voicevault_id', $voicevault_id, 1);

		if ($this->response_type == 'json') {
			$data = array(
				'error' => !$success,
				'message' => (!$success ? 'an error occurred while updating the user' : 'user status updated')
			);
			$this->respond('', null, $data);
		}
		else {
			if ($success) {
				$this->session->set_flashdata('message_edit', 'User data changed');
				redirect('account');
			}
			else {
				$this->data['error_edit'] = '';
				$this->index();
			}
		}
	}

	public function password()
	{
		if (!$this->session->userdata('loggedin')) redirect('auth/login');

		$user = VBX_user::get(array('id' => $this->user_id));

		$old_pw = $this->input->post('old_pw');
		$new_pw = $this->input->post('new_pw1');
		$new_pw2 = $this->input->post('new_pw2');
		$this->data['error'] = false;
		$message = '';

		if($user->password != VBX_User::salt_encrypt($old_pw))
		{
			$this->data['error'] = true;
			$message = 'Password incorrect';
		}
		else if($new_pw != $new_pw2)
		{
			$this->data['error'] = true;
			$message = 'Password mismatch';
		}
		else
		{
			$user->password = VBX_User::salt_encrypt($new_pw);
			try
			{
				$user->save();
				$message = 'Password changed';
				$this->session->set_userdata('signature', VBX_User::signature($user->id));
			}
			catch(VBX_UserException $e)
			{
				$this->data['error'] = true;
				$message = 'Unable to set password, please try again later.';
				error_log($e->getMessage());
			}
		}
		$this->data['message'] = $message;

		echo json_encode($this->data);
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
	
}
