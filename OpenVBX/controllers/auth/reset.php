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

class Reset extends MY_Controller
{
	protected $user_id;

	function __construct()
	{
		parent::__construct();
		$this->config->load('openvbx');
		$this->load->database();

		$this->template->write('title', '');

		$this->user_id = $this->session->userdata('user_id');
	}

	public function index()
	{
		return $this->reset();
	}

	public function set_password($invite_code = '')
	{
		if(empty($invite_code))
			return redirect('auth/login');


		$user = VBX_User::get(array('is_active' => 1,
									'invite_code' => $invite_code));

		if(!$user)
			return redirect('auth/login');

		$data = array('invite_code' => $invite_code);

		if(isset($_POST['password']))
		{
			try
			{
				$user->set_password($_POST['password'], $_POST['confirm']);
				return redirect('auth/login');
			} catch(VBX_UserException $e) {
				$data['error'] = $e->getMessage();
				$this->session->set_flashdata($e->getMessage());
			}
		}

		return $this->respond('', 'set-password', $data, 'login-wrapper', 'layout/login');
	}

	public function reset()
	{
		$this->template->write('title', 'Reset Password');
		$data = array();
		$email = $this->input->post('email');
		$phone = $this->input->post('phone');

		// Check for voice vault 
		if ($this->voicevault_username &&
				$this->voicevault_password &&
				$this->voicevault_config &&
				$this->voicevault_organisation) {
			$data['voicevault_enabled'] = true;
		} else {
			$data['voicevault_enabled'] = false;
		}

		if ($data['voicevault_enabled'] && $phone && $email) {
			// Initiate a curl request to voicevault
			return redirect("voicevault/web/reset/$email");
		}

		if(empty($email))
		{
			// $data['error'] = $this->session->flashdata('error');
			if (isset($_POST['email']))
				$data['error'] = "Email field required.";
			return $this->respond('', 'reset', $data, 'login-wrapper', 'layout/login');
		}

		$user = VBX_User::get(array('email' => $this->input->post('email'),
									'is_active' => 1,
									));
		if(empty($user))
		{
			$this->session->set_flashdata('error',
										  'No active account found.');

			return redirect('auth/reset');
		}

		if($user->auth_type == 'google')

		{
			header('Location: http://www.google.com/support/accounts/bin/answer.py?answer=48598&hl=en&ctx=ch_Login&fpUrl=https%3A%2F%2Fwww.google.com%2Faccounts%2FForgotPasswd%3FfpOnly%3D1%26continue%3Dhttp%253A%252F%252Fwww.google.com%252F%26hl%3Den');
			return;
		}
		else
		{
			$user = new VBX_User($user);
			$user->send_reset_notification();
			$this->session->set_flashdata('error',
										  'To complete the password reset, check your inbox.');
			return redirect('auth/login');
		}

		return redirect('auth/reset');
	}

}