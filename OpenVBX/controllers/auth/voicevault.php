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

class VoiceVault extends MY_Controller
{
	protected $user_id;

	function __construct()
	{
		parent::__construct();
		$this->config->load('openvbx');
		$this->load->database();

		$this->template->write('title', '');

		$this->user_id = $this->session->userdata('user_id');

		if (!$this->user_id)
		{
			return redirect('auth/reset');
		}
	}

	public function index()
	{
		return $this->voicevault();
	}

	public function voicevault()
	{
		if (!empty($_POST) && isset($_POST['pin']))
		{
			$pin = $this->input->post('pin');

			$user = VBX_User::get(array('id'=>$this->user_id));
			$login = $user->login_openvbx($user, $pin);

			// Login successful
			if ($login) 
			{
				// Send user to reset password dialog
				$this->session->set_userdata('pin_verified', true);
				$this->session->set_flashdata('error', 
					'Pin verified successfully.');
				return redirect('auth/voicevault/reset');
			}
			else 
			{
				// Invalid login, redirect user to front
				$this->session->set_flashdata('error', 'Invalid PIN.');
				return redirect('auth/voicevault');
			}
		}

		$data = array();
		$data['error'] = $this->session->flashdata('error');

		return $this->respond('', 'voicevault', $data, 'login-wrapper', 'layout/login');
	}

	public function reset()
	{
		$verified = $this->session->userdata('pin_verified');

		// Pin hasn't been verified, restrict access
		if (!$verified)
		{
			return redirect('auth/reset');
		}

		// Verified, reset password
		if (!empty($_POST) && isset($_POST['password']) &&
			isset($_POST['password_confirm']))
		{
			$password = $this->input->post('password');
			$password_confirm = $this->input->post('password_confirm');

			$user = VBX_User::get(array('id'=>$this->user_id));

			if ($password == $password_confirm) 
			{
				// Reset password
				if ($user->set_password($password, $password_confirm)) 
				{
					$this->session->set_flashdata('error',
						'Password reset successfully.');
					// Login
					return redirect('auth/login');
				}
				else
				{
					// Invalid password
					$this->session->set_flashdata('error',
						'Error updating user password.');
					return redirect('auth/voicevault/reset');
				}
			}
			else
			{
				// Invalid password, set notification
				$this->session->set_flashdata('error',
					'Passwords do not match.');
				return redirect('auth/voicevault/reset');
			}
		}

		$data['error'] = $this->session->flashdata('error');

		return $this->respond('', 'voicevault_reset', $data, 
			'login-wrapper', 'layout/login');
	}

}