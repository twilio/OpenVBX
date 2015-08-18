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
	protected $js_assets = 'loginjs';

	function __construct()
	{
		parent::__construct();
		$this->config->load('openvbx');
		$this->load->database();
		$this->template->write('title', '');
		
		// no cache
		$ci =& get_instance();
		$ci->cache->enabled(false);

		$this->user_id = $this->session->userdata('user_id');
	}

	public function index()
	{
		$redirect = $this->input_redirect();
		
		if($this->session->userdata('loggedin'))
		{
			if(VBX_User::signature($this->user_id) == $this->session->userdata('signature'))
			{
				return $this->redirect($redirect);
			}
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

		return ltrim($redirect, '/');
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
			$user = VBX_User::login($this->input->post('email'),
									$this->input->post('pw'),
									$this->input->post('captcha'),
									$this->input->post('captcha_token'));

			if ($user) {
				$connect_auth = OpenVBX::connectAuthTenant($user->tenant_id);

				// we kick out non-admins, admins will have an opportunity to re-auth the account
				if (!$connect_auth && !$user->is_admin) 
				{
					$this->session->set_flashdata('error', 'Connect auth denied');
					return redirect('auth/connect/account_deauthorized');
				}

				$userdata = array(
					'email' => $user->email,
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

				$this->redirect($redirect);
			}
			
			$this->session->set_flashdata('error',
										  'Email address and/or password is incorrect');
			redirect('auth/login?redirect='.urlencode($redirect));
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
	
		// if the redirect would take us back to
		// the iframe the nuke it
		if ($redirect == site_url())
		{
			$redirect = '';
		}
	
		// Redirect to flows if this is an admin and his inbox is zero 
		// (but not if the caller is hitting the REST api)
		if($this->response_type != 'json' && empty($redirect))
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
						$banner = array(
							'id' => 'first-login',
							'html' => $this->load->view('banners/first-login', array(), true),
							'title' => 'Welcome to OpenVBX'
						);
						$path = '/'.(($this->tenant->id > 1)? $this->tenant->name : '');
						setrawcookie('banner', rawurlencode(json_encode($banner)), 0, $path);
						set_last_known_url(site_url('/numbers'));
						redirect('');
					}
				}
				catch(VBX_IncomingNumberException $e)
				{
					// Handle gracefully but log it
					log_message('error', $e->getMessage());
				}
			}

			$devices = VBX_Device::search(array('user_id' => $user->id));
			if(empty($devices))
			{
				set_last_known_url(site_url('/devices'));
				redirect('');
			}
		}
		
		set_last_known_url($redirect);
		$this->redirect('');
	}
}
