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

class Login extends MY_Controller
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
		$redirect = $this->input_redirect();

		if (strpos($redirect, '/') === 0) {
			$redirect = substr($redirect, 1);
		}

		if($this->session->userdata('loggedin'))
		{
			if(VBX_User::signature($this->user_id) == $this->session->userdata('signature'))
				return $this->redirect($redirect);
		}
		
		$this->template->write('title', 'Log In');
		$data = array();
		$data['redirect'] = $redirect;
		
		if($this->input->post('login'))
		{
			$this->login($redirect);
		}

		// admin check sets flashdata error message
		if(!isset($data['error']))
		{
			$error = $this->session->flashdata('error');
			if(!empty($error)) $data['error'] = CI_Template::literal($error);
		}


		return $this->respond('', 'login', $data, 'login-wrapper', 'layout/login');
	}

	private function input_redirect()
	{
		$redirect = $this->input->get('redirect');
		
		if(!empty($redirect))
		{
			$this->session->set_flashdata('redirect', $redirect);
		}
		else
		{
			$redirect = $this->session->flashdata('redirect');
		}

		return $redirect;
	}
	
	private function redirect($redirect)
	{
		$redirect = preg_replace('/^(http|https):\/\//i', '', $redirect);
		redirect($redirect);
	}
	
	private function login($redirect)
	{
		try
		{
			$user = VBX_User::authenticate($this->input->post('email'),
										   $this->input->post('pw'),
										   $this->input->post('captcha'),
										   $this->input->post('captcha_token'));
			
			if($user)
			{
				$userdata = array('email' => $user->email,
								  'user_id' => $user->id,
								  'is_admin' => $user->is_admin,
								  'loggedin' => TRUE,
								  'signature' => VBX_User::signature($user->id),
								  );
				
				$this->session->set_userdata($userdata);

				if(OpenVBX::schemaVersion() >= 24)
				{
					return $this->after_login_completed($user, $redirect);
				}

				return $this->redirect($redirect);
				
			}

			$this->session->set_flashdata('error',
										  'Email address and/or password is incorrect');
			return redirect('auth/login?redirect='.urlencode($redirect));
		}
		catch(GoogleCaptchaChallengeException $e)
		{
			$this->session->set_flashdata('error', $e->getMessage());

			$data['error'] = $e->getMessage();
			$data['captcha_url'] = $e->captcha_url;
			$data['captcha_token'] = $e->captcha_token;
		}
	}

	protected function after_login_completed($user, $redirect)
	{
		$last_seen = $user->last_seen;
	
		/* Redirect to flows if this is an admin and his inbox is zero (but not if the caller is hitting the REST api)*/
		if($this->response_type != 'json')
		{
			$is_admin = $this->session->userdata('is_admin');
			if($is_admin)
			{
				$this->load->model('vbx_incoming_numbers');
				$twilio_numbers = array();
				try
				{
					$twilio_numbers = $this->vbx_incoming_numbers->get_numbers();
					if(empty($twilio_numbers))
					{
						$banner = array('id' => 'first-login',
										'html' => 'To start setting up OpenVBX, we suggest you start out with building your first <a href="'.site_url('flows').'">call flow</a>. ',
										'title' => 'Welcome to OpenVBX');
						setrawcookie('banner',
									 rawurlencode(json_encode($banner)),
									 0,
									 '/'.(($this->tenant->id > 1)? $this->tenant->name : '')
									 );
						setcookie('last_known_url', site_url('/numbers'), null, '/');
						return redirect('');
					}
				}
				catch(VBX_IncomingNumberException $e)
				{
					/* Handle gracefully but log it */
					error_log($e->getMessage());
				}
			}

			$devices = VBX_Device::search(array('user_id' => $user->id));
			if(empty($devices))
			{
				setcookie('last_known_url', site_url('/devices'), null, '/');
				return redirect('');
			}
		}
		
		setcookie('last_known_url', $redirect, null, '/');
		return $this->redirect('');
	}
}
