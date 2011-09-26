<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 *
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.
 *
 *  The Original Code is OpenVBX, released June 15, 2010.
 *
 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.
 *
 * Contributor(s):
 **/

require_once(APPPATH.'libraries/twilio.php');

class SiteException extends Exception {}

class Site extends User_Controller
{
	const MODE_MULTI = 1;
	const MODE_SINGLE = 2;

	function __construct()
	{
		parent::__construct();
		$this->load->model('vbx_theme');
		$this->section = 'site settings';
		$this->admin_only($this->section);
	}

	public function index($action = '', $id = false)
	{
		return $this->site($action, $id);
	}

	private function site($action, $id)
	{
		$this->section = 'settings/site';

		switch($action)
		{
			case 'tenant':
				return $this->tenant_handler($id);
			default:
				return $this->site_handler();
		}
	}

	private function site_handler()
	{
		switch($this->request_method)
		{
			case 'POST':
				return $this->update_site();
			default:
				return $this->get_site();
		}
	}

	private function tenant_handler($id)
	{
		switch($this->request_method)
		{
			case 'POST':
				if($id)
				{
					return $this->update_tenant($id);
				}
				return $this->add_tenant();
			case 'GET':
				if($id)
				{
					return $this->get_tenant($id);
				}
			default:
				return redirect('settings/site');
		}
	}

	private function get_current_settings($id = false)
	{
		if(!$id)
		{
			$id = $this->tenant->id;
		}

		$current_settings = $this->settings->get_all_by_tenant_id($id);

		$sorted_settings = array();
		foreach($current_settings as $setting)
		{
			$sorted_settings[$setting->name] = array(
													 'id' => $setting->id,
													 'value' => $setting->value
													 );
		}

		return $sorted_settings;
	}

	private function get_site()
	{
		$this->template->add_js('assets/j/settings.js');

		$data = $this->init_view_data();
		$current_settings = $this->get_current_settings();
		$data = array_merge($data, $current_settings);
		$data['tenant_mode'] = self::MODE_SINGLE;
		if($this->tenant->name == 'default')
		{
			$data['tenant_mode'] = self::MODE_MULTI;
			$data['tenants'] = $this->settings->get_all_tenants();
		}
		else {
			// allow tenants to see the rewrite setting
			$data['rewrite_enabled'] = array(
				'value' => intval($this->settings->get('rewrite_enabled', VBX_PARENT_TENANT))
			);
		}

		// Get numbers (for voice vault)
		$vbx_numbers = $this->vbx_incoming_numbers->get_numbers();
		$data['numbers'] = array();
		foreach ($vbx_numbers as $number) {
			if ($number && $number->phone && $number->raw_phone &&
				$number->api_type == 'tropo')
			{
				$data['numbers'][$number->raw_phone] = $number->phone;
			}
		}

		$data['available_themes'] = $this->vbx_theme->get_all();
		$plugins = Plugin::all();
		foreach($plugins as $plugin)
		{
			$data['plugins'][] = $plugin->getInfo();
		}
		$data['error'] = $this->session->flashdata('error');

		$data['json']['settings'] = $current_settings;

		$this->respond('Site Settings', 'settings/site', $data);
	}

	private function update_site()
	{
		$data = array('message' => '', 'error' => false);
		$site = $this->input->post('site');

		$current_app_sid = $this->settings->get('app_sid', VBX_PARENT_TENANT);

		if(!empty($site))
		{
			try
			{
				foreach($site as $name => $value)
				{
					if ($name == 'application_sid')
					{
						$app_sid = $value;
					}
					$this->settings->set($name, trim($value), $this->tenant->id);
				}

				$update_app = false;
				if (empty($app_sid) && !empty($current_app_sid))
				{
					// disassociate the current app from this install
					$update_app[] = array(
										  'app_sid' => $current_app_sid,
										  'params' => array(
															'VoiceUrl' => '',
															'VoiceFallbackUrl' => '',
															'SmsUrl' => '',
															'SmsFallbackUrl' => ''
															)
										  );
				}
				elseif (!empty($app_sid))
				{
					// update the application data
					$update_app[] = array(
										  'app_sid' => $app_sid,
										  'params' => array(
															'VoiceUrl' => site_url('/twiml/dial'),
															'VoiceFallbackUrl' => site_url('/fallback/voice.php'),
															'VoiceMethod' => 'POST',
															'SmsUrl' => '',
															'SmsFallbackUrl' => '',
															'SmsMethod' => 'POST'
															)
										  );

					if ($app_sid != $current_app_sid) {
						// app sid changed, disassociate the old app from this install
						$update_app[] = array(
											  'app_sid' => $current_app_sid,
											  'params' => array(
																'VoiceUrl' => '',
																'VoiceFallbackUrl' => '',
																'SmsUrl' => '',
																'SmsFallbackUrl' => ''
																)
											  );
					}
				}

				if (!empty($update_app))
				{
					$twilio = new TwilioRestClient($this->twilio_sid,
												   $this->twilio_token,
												   $this->twilio_endpoint);

					foreach ($update_app as $app)
					{
						$response = $twilio->request('Accounts/'.$this->twilio_sid.'/Applications/'.$app['app_sid'],
													 'POST',
													 $app['params']);

						if($response && $response->ResponseXML->IsError)
						{
							$this->session->set_flashdata('error', $response->ResponseXML->ErrorMessage);
							throw new SiteException($response->ResponseXML->ErrorMessage);
						}
					}
				}

				$this->session->set_flashdata('error', 'Settings have been saved');
			}
			catch(SiteException $e)
			{
				$data['error'] = true;
				$data['message'] = $e->getMessage();
				$this->session->set_flashdata('error', $e->getMessage());
			}
		}

		if($this->response_type == 'html')
		{
			redirect('settings/site');
		}

		$this->respond('', 'settings/site', $data);
	}

	private function create_application_for_subaccount($tenant_id, $name, $accountSid) {
		$appName = "OpenVBX - {$name}";
		$twilio = new TwilioRestClient($this->twilio_sid,
									   $this->twilio_token,
									   $this->twilio_endpoint);
		$response = $twilio->request("Accounts/{$accountSid}/Applications",
									 "GET",
									 array("FriendlyName" => $appName));

		if($response->IsError) {
			if($response->HttpStatus > 400) {
				throw(new VBX_SettingsException($response->ErrorMessage));
			}
        }

		// If we found an existing application, update the urls.
        $foundApp = intval($response->ResponseXml->Applications['total']);
        if($foundApp) {
			$appSid = (string)$response->ResponseXml->Applications->Application->Sid;
			$response = $twilio->request("Accounts/{$accountSid}/Applications/{$appSid}",
										 'POST',
										 array('FriendlyName' => $appName,
											   'VoiceUrl' => tenant_url('twiml/dial', $tenant_id),
											   'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
											   'VoiceMethod' => 'POST',
											   'SmsUrl' => '',
											   'SmsFallbackUrl' => '',
											   'SmsMethod' => 'POST',
											   ));
			if($response->IsError) {
				if($response->HttpStatus > 400) {
					throw(new VBX_SettingsException("Failed to create application: " . $response->ErrorMessage));
				}
			}

			// Otherwise, lets create a new application for openvbx
        } else {
			$response = $twilio->request("Accounts/{$accountSid}/Applications",
										 'POST',
										 array('FriendlyName' => $appName,
											   'VoiceUrl' => tenant_url('twiml/dial', $tenant_id),
											   'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
											   'VoiceMethod' => 'POST',
											   'SmsUrl' => '',
											   'SmsFallbackUrl' => '',
											   'SmsMethod' => 'POST',
											   ));
			if($response->IsError) {
				if($response->HttpStatus > 400) {
					throw(new VBX_SettingsException("Failed to create application: " . $response->ErrorMessage));
				}
			}

			$appSid = (string)$response->ResponseXml->Application->Sid;
        }

		return $appSid;
	}

	private function add_tenant()
	{
		$tenant = $this->input->post('tenant');
		if(!empty($tenant))
		{
			try
			{
				$data['id'] = $this->settings->tenant($tenant['url_prefix'],
													  urlencode($tenant['url_prefix']),
													  '');

				$this->db->trans_start();
				$user = new VBX_User();
				$user->fields[] = 'tenant_id'; // monkey patching to override tenant_id
				$user->first_name = '';
				$user->last_name = '';
				$user->password = '';
				$user->values['tenant_id'] = $data['id']; // hidden field not in ORM
				$user->email = $tenant['admin_email'];
				$user->is_active = TRUE;
				$user->is_admin = TRUE;
				$user->auth_type = 1;
				try
				{
					$user->save();
					$user->send_new_user_notification();
				}
				catch(VBX_UserException $e)
				{
					throw new VBX_SettingsException($e->getMessage());
				}


				foreach($this->settings->setting_options as $param)
				{
					$this->settings->add($param, '', $data['id']);
				}

				$this->settings->set('from_email', $tenant['admin_email'], $data['id']);
				try
				{
					$twilio = new TwilioRestClient($this->twilio_sid,
												   $this->twilio_token,
												   $this->twilio_endpoint);
					$friendlyName = $tenant['url_prefix'] . ' - ' . $tenant['admin_email'];
					$friendlyName = substr($friendlyName, 0, 32);
					$response = $twilio->request("Accounts", 'POST', array('FriendlyName' =>
																		   $friendlyName
																		   ));
					if($response
					   && $response->IsError != true)
					{
						$account = $response->ResponseXml;
						$tenant_sid = (String)$account->Account->Sid;
						$tenant_token = (String)$account->Account->AuthToken;
						$this->settings->add('twilio_sid', $tenant_sid, $data['id']);
						$this->settings->add('twilio_token', $tenant_token, $data['id']);
					}
					else
					{
						$message = 'Failed to create new subaccount';
						if($response && $response->ErrorMessage)
							$message = $response->ErrorMessage;
						throw new VBX_SettingsException($message);
					}

					$appSid = $this->create_application_for_subaccount($data['id'], $tenant['url_prefix'], $tenant_sid);

					$this->settings->add('application_sid', $appSid, $data['id']);
				}
				catch(Exception $e) {
					throw new VBX_SettingsException($e->getMessage());
				}

				$this->db->trans_complete();
				$this->session->set_flashdata('error', 'Added new tenant');

				if(isset($data['id']))
				{
					return redirect('settings/site/tenant/'.$data['id']);
				}
			}
			catch(VBX_SettingsException $e)
			{
				error_log($e->getMessage());
				$this->db->trans_rollback();
				// TODO: rollback in twilio.
				$this->session->set_flashdata('error', $e->getMessage());
				$data['error'] = true;
				$data['message'] = $e->getMessage();
			}

		}

		if($this->response_type == 'html')
		{
			redirect('settings/site');
		}

		$this->respond('', 'settings/site', $data);
	}

	public function get_tenant($id)
	{
		$data = $this->init_view_data();
		$tenant = $this->settings->get_tenant_by_id($id);
		$tenant_settings = $this->get_current_settings($id);

		$data['tenant'] = $tenant;
		$data['tenant_settings'] = $tenant_settings;
		$data['available_themes'] = $this->vbx_theme->get_all();
		$data['rewrite_enabled'] = array(
			'value' => intval($this->settings->get('rewrite_enabled', VBX_PARENT_TENANT))
		);

		$this->respond('Tenant Settings', 'settings/tenant', $data);
	}

	private function update_tenant($id)
	{
		$tenant = $this->input->post('tenant');
		$tenant_settings = $this->input->post('tenant_settings');

		$tenant['id'] = $id;
		try
		{
			$this->settings->update_tenant($tenant);
			foreach($this->settings->setting_options as $param)
			{
				if(isset($tenant_settings[$param]))
				{
					$this->settings->set($param, trim($tenant_settings[$param]), $id);
				}
			}
		}
		catch(SettingsException $e)
		{
			$this->session->set_flashdata('error', $e->getMessage());
			$data['error'] = true;
			$data['message'] = $e->getMessage();
		}

		if($this->response_type == 'html')
		{
			redirect('settings/site/tenant/'.$id);
		}

		$this->respond('', 'settings/tenant', $data);
	}

}
