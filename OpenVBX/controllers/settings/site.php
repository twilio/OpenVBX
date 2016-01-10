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

class SiteException extends Exception {}

/**
 * Class Site
 * @property CI_DB_Driver|CI_DB_mysql_driver $db
 * @property VBX_Theme $vbx_theme
 */
class Site extends User_Controller
{
	const MODE_MULTI = 1;
	const MODE_SINGLE = 2;

	protected $form_action;

	function __construct()
	{
		parent::__construct();
		$this->load->model('vbx_theme');
		$this->section = 'site settings';
		$this->admin_only($this->section);
	}

	public function index($action = 'site', $id = false)
	{
		return $this->site($action, $id);
	}

	private function site($action, $id)
	{
		$this->section = 'settings/site';
		$this->form_action = $action;

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
				return redirect('settings/site#multi-tenant');
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

		// insert the server's default time zone in the event none is saved
		if (empty($current_settings['server_time_zone']))
		{
			$current_settings['server_time_zone'] = array(
				'id' => null,
				'value' => date_default_timezone_get()
			);
		}

		$current_settings['cache_enabled'] = $this->cache->enabled();
		$current_settings['api_cache_enabled'] = $this->api_cache->enabled();

		$data = array_merge($data, $current_settings);
		$data['tenant_mode'] = self::MODE_SINGLE;

		$data['openvbx_version'] = OpenVBX::version();
		
		// determine wether we can successfully use the GitHub api library
		// to check our current tag against available tags. See ::can_check_upgrade()
		// for a full explanation.
		// @todo - find a more graceful way around this
		// @todo - notify admin that checks can't be made?
		$data['check_upgrade'] = $this->can_check_upgrade();
		
		if($this->tenant->name == 'default')
		{
			$data['tenant_mode'] = self::MODE_MULTI;
			$data['tenants'] = $this->settings->get_all_tenants();
			
			if ($data['check_upgrade']) {
				$data['latest_version'] = $this->get_latest_tag();
		
				if (version_compare($data['openvbx_version'], $data['latest_version'], '<'))
				{
					$data['upgrade_notice'] = true;
				}
			}
		}

		// allow tenants to see the rewrite setting
		$data['rewrite_enabled'] = array(
			'value' => intval($this->settings->get('rewrite_enabled', VBX_PARENT_TENANT))
		);

		if ($this->db->dbdriver == 'mysqli')
		{
			$mysql_version = $this->db->conn_id->server_info;
		}
		else 
		{
			$mysql_version = mysql_get_server_info($this->db->conn_id);
		}

		$data['server_info'] = array(
			'system_version' => php_uname(),
			'php_version' => phpversion(),
			'php_sapi' => php_sapi_name(),
			'mysql_version' => $mysql_version,
			'mysql_driver' => $this->db->dbdriver,
			'apache_version' => $_SERVER['SERVER_SOFTWARE'],
			'current_url' => site_url($this->uri->uri_string()).' ('.$_SERVER['SERVER_ADDR'].')'
		);

		$data['available_themes'] = $this->get_available_themes();
		
		// get plugin data
		$plugins = Plugin::all();
		foreach($plugins as $plugin)
		{
			$data['plugins'][] = $plugin->getInfo();
		}
		$data['error'] = $this->session->flashdata('error');

		$data['json']['settings'] = $current_settings;

		// build list of time zones
		$tzs = timezone_identifiers_list();
		$data['time_zones'] = array_combine($tzs, $tzs); // makes keys & values match

		// get list of available countries
		$this->load->model('vbx_incoming_numbers');
		$data['countries'] = array();
		try {
			if ($countrydata = $this->vbx_incoming_numbers->get_available_countries())
			{
				foreach ($countrydata as $country)
				{
					$data['countries'][$country->country_code] = $country->country;
				}
			}
		}
		catch (VBX_IncomingNumberException $e)
		{
			$data['error'] = 'Unable to fetch available countries: ';
			switch ($e->getCode())
			{
				case 0;
					$data['error'] .= 'Authentication failed.';
					break;
				default:
					$data['error'] .= $e->getMessage();
			}
		}

		// load language codes for text-to-speech
		$this->config->load('langcodes');
		$data['lang_codes'] = $this->config->item('lang_codes');

		// verify Client Application data
		$data['client_application_error'] = false;
		$account = OpenVBX::getAccount();
		$application = $account->applications->get($data['application_sid']['value']);
		if (!empty($data['application_sid']['value']))
		{
			try {
				// only way to be sure on these is to pull them in to variables, ugh...
				$application_sid = $application->sid;
				$application_voice_url = $application->voice_url;
				$application_voice_fallback_url = $application->voice_fallback_url;
				if (strlen($application_sid) == 0)
				{
					// application missing
					$data['client_application_error'] = 2;
				}
				elseif (strlen($application_voice_url) == 0 || 
						strlen($application_voice_fallback_url) == 0)
				{
					// urls are missing
					$data['client_application_error'] = 3;
				}
				elseif ($application_voice_url != site_url('/twiml/dial') ||
					$application_voice_fallback_url != asset_url('fallback/voice.php'))
				{
					// url mismatch
					$data['client_application_error'] = 4;
				}
			}
			catch (Exception $e) {
				$data['client_application_error'] = 5;
				$data['error'] = 'Could not validate Client Application data: '.$e->getMessage();
				$data['client_application_error_message'] = $e->getMessage();
				log_message($e->getMessage());
			}
		}
		else
		{
			$data['client_application_error'] = 1;
		}
		$data['client_application'] = $application;
		$data['site_revision'] = $this->config->item('site_rev');

		$this->respond('Site Settings', 'settings/site', $data);
	}

	private function update_site()
	{
		$data = array('message' => '', 'error' => false);
		$site = $this->input->post('site');

		$process_app = false;
		$process_connect_app = false;

		$notification_settings = array(
			'email_notifications_voice',
			'email_notifications_sms'
		);

		if(!empty($site))
		{
			try {
				foreach($site as $name => $value)
				{
					if (in_array($name, $notification_settings))
					{
						continue;
					}
					
					if ($name == 'application_sid')
					{
						$app_sid = $value;
						$process_app = true;
					}
					
					if ($name == 'connect_application_sid') 
					{
						$connect_app_sid = $value;
						$process_connect_app = true;
					}
					
					// add new settings if they don't already exist
					if (!$this->settings->set($name, trim($value), $this->tenant->id))
					{
						$this->settings->add($name, trim($value), $this->tenant->id);
					}
				}

				if ($this->form_action == 'site')
				{
					foreach ($notification_settings as $name)
					{
						$value = (!empty($site[$name]) ? 1 : 0);
						$this->settings->add($name, $value, $this->tenant->id);
					}

				}

				// Connect App (if applicable)
				if ($process_connect_app)
				{
					$this->update_connect_app($connect_app_sid);
				}
				
				// Client App
				if ($process_app)
				{
					$this->update_application($app_sid);
				}
				
				$this->session->set_flashdata('error', 'Settings have been saved');
			}
			catch(Exception $e) {
				$data['error'] = true;
				switch($e->getCode()) 
				{
					case '0':
						$data['message'] = $message = 'Could not Authenticate with Twilio. '.
														'Please check your Sid & Token values.';
						break;
					default:
						$data['message'] = $message = $e->getMessage();
				}

				$this->session->set_flashdata('error', $message);
			}
		}
		
		flush_minify_caches();

		$returnSection = '';
		switch($this->form_action) {
			case 'account':
				$returnSection = '#twilio-account';
				break;
			case 'theme':
				$returnSection = '#theme';
				break;
			default;
				$returnSection = '#system-config';
		}

		if($this->response_type == 'html')
		{
			redirect('settings/site' . $returnSection);
		}

		$this->respond('', 'settings/site' . $returnSection, $data);
	}

	private function update_application($app_sid)
	{
		$update_app = false;
		$current_app_sid = $this->settings->get('application_sid', $this->tenant->id);
		
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
					'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
					'VoiceMethod' => 'POST',
					'SmsUrl' => '',
					'SmsFallbackUrl' => '',
					'SmsMethod' => 'POST'
				)
			);

			if ($app_sid != $current_app_sid) 
			{
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
			$account = OpenVBX::getAccount();

			foreach ($update_app as $app) 
			{
				try {
					/** @var Services_Twilio_Rest_Application $application */
					$application = $account->applications->get($app['app_sid']);
					$application->update(array_merge($app['params'], array(
									'FriendlyName' => $application->friendly_name
								)));
				}
				catch (Exception $e) {
					$this->session->set_flashdata('error', 'Could not update '.
												'Application: '.$e->getMessage());
					throw new SiteException($e->getMessage(), $e->getCode());
				}
			}					
		}
	}

	private function update_connect_app($connect_app_sid)
	{
		if (!empty($connect_app_sid) && $this->tenant->id == VBX_PARENT_TENANT) 
		{
			$account = OpenVBX::getAccount();
			/** @var Services_Twilio_Rest_ConnectApp $connect_app */
			$connect_app = $account->connect_apps->get($connect_app_sid);
		
			$required_settings = array(
				'HomepageUrl' => site_url(),
				'AuthorizeRedirectUrl' => site_url('/auth/connect'),
				'DeauthorizeCallbackUrl' => site_url('/auth/connect/deauthorize'),
				'Permissions' => array(
					'get-all',
					'post-all'
				)
			);
		
			$updated = false;
			foreach ($required_settings as $key => $setting) 
			{
				$app_key = Services_Twilio::decamelize($key);
				if ($connect_app->$app_key != $setting) 
				{
					$connect_app->$app_key = $setting;
					$updated = true;
				}
			}
		
			if ($updated) 
			{
				$connect_app->update(array(
					'FriendlyName' => $connect_app->friendly_name,
					'Description' => $connect_app->description,
					'CompanyName' => $connect_app->company_name,
					'HomepageUrl' => $required_settings['HomepageUrl'],
					'AuthorizeRedirectUrl' => $required_settings['AuthorizeRedirectUrl'],
					'DeauthorizeCallbackUrl' => $required_settings['DeauthorizeCallbackUrl'],
					'Permissions' => implode(',', $required_settings['Permissions'])
				));
			}
		}
	}

	private function create_application_for_subaccount($tenant_id, $name, $accountSid) 
	{
		$appName = "OpenVBX - {$name}";
		
		$application = false;
		try {
			/** @var Services_Twilio_Rest_Accounts $accounts */
			$accounts = OpenVBX::getAccounts();
			$sub_account = $accounts->get($accountSid);
			foreach ($sub_account->applications as $_application) 
			{
				if ($_application->friendly_name == $appName)
				{
					/** @var Services_Twilio_Rest_Application $application */
					$application = $_application;
				}
			}
		}
		catch (Exception $e) {
			throw new VBX_SettingsException($e->getMessage());
		}

		$params = array(
			'FriendlyName' => $appName,
			'VoiceUrl' => tenant_url('twiml/dial', $tenant_id),
			'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
			'VoiceMethod' => 'POST',
			'SmsUrl' => '',
			'SmsFallbackUrl' => '',
			'SmsMethod' => 'POST'
		);

		try {
			if (!empty($application)) 
			{
				$application->update($params);
			}
			else 
			{
				$application = $sub_account->applications->create($appName, $params);
			}
		}
		catch (Exception $e) {
			throw new VBX_SettingsException($e->getMessage(), $e->getCode());
		}

		return $application->sid;
	}

	private function add_tenant()
	{
		$tenant = $this->input->post('tenant');
		
		if (empty($tenant['url_prefix']))
		{
			$data['error'] = true;
			$data['message'] = 'A valid tenant name is required';
			$this->session->set_flashdata('error', 'Failed to add new tenant: '.$data['message']);
		}
		if (empty($tenant['admin_email']) || 
			!filter_var($tenant['admin_email'], FILTER_VALIDATE_EMAIL))
		{
			$data['error'] = true;
			$data['message'] = 'A valid admin email address is required';
			$this->session->set_flashdata('error', 'Failed to add new tenant: '.$data['message']);
		}
		
		if(!empty($tenant) && empty($data['error']))
		{
			try {
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

				try {
					$user->save();
				}
				catch(VBX_UserException $e) {
					throw new VBX_SettingsException($e->getMessage());
				}

				foreach($this->settings->setting_options as $param)
				{
					$this->settings->add($param, '', $data['id']);
				}

				$this->settings->set('from_email', $tenant['admin_email'], $data['id']);
				$friendlyName = substr($tenant['url_prefix'].' - '.$tenant['admin_email'], 0, 32);					

				switch ($this->input->post('auth_type')) 
				{
					case 'connect':
						$auth_type = VBX_Settings::AUTH_TYPE_CONNECT;
						break;
					case 'subaccount':
					default:
						$auth_type = VBX_Settings::AUTH_TYPE_SUBACCOUNT;
						break;
				}

				/**
				 * Only do app setup for sub-accounts.
				 * Connect tenants will get set up after going through the connect process.
				 */
				if ($auth_type === VBX_Settings::AUTH_TYPE_SUBACCOUNT) 
				{
					try {
						/** @var Services_Twilio_Rest_Accounts $accounts */
						$accounts = OpenVBX::getAccounts();

						// default, sub-account
						$sub_account = $accounts->create(array(
														'FriendlyName' => $friendlyName
													));

						$tenant_sid = $sub_account->sid;
						$tenant_token = $sub_account->auth_token;
						$this->settings->add('twilio_sid', $tenant_sid, $data['id']);
						$this->settings->add('twilio_token', $tenant_token, $data['id']);
						
						$app_sid = $this->create_application_for_subaccount($data['id'], 
													$tenant['url_prefix'], 
													$tenant_sid);
						$this->settings->add('application_sid', $app_sid, $data['id']);
					}
					catch (Exception $e) {
						throw new VBX_SettingsException($e->getMessage());
					}
				}
				elseif ($auth_type === VBX_Settings::AUTH_TYPE_CONNECT)
				{
					// when using connect, we won't get a sid, token, or 
					// app_sid until user first login
					$tenant_id = $tenant_token = $app_sid = null;
					$this->settings->add('tenant_first_run', 1, $data['id']);
				}
				else 
				{
					throw new VBX_SettingsException('Unknown auth-type encountered during '.
													'tenant creation');
				}

				$this->settings->update_tenant(array(
					'id' => $data['id'],
					'type' => $auth_type
				));
				
				$tenant_defaults = array(
					'transcriptions' => 1,
					'voice' => 'man',
					'voice_language' => 'en',
					'numbers_country' => 'US',
					'gravatars' => 0,
					'dial_timeout' => 15
				);
				foreach ($tenant_defaults as $key => $value) {
					$this->settings->set($key, $value, $data['id']);
				}
				
				$this->db->trans_complete();
				$this->session->set_flashdata('error', 'Added new tenant');
				$user->send_new_user_notification();

				if(isset($data['id']))
				{
					return redirect('settings/site/tenant/'.$data['id']);
				}
			}
			catch(VBX_SettingsException $e) {
				error_log($e->getMessage());
				$this->db->trans_rollback();
				// TODO: rollback in twilio.
				$this->session->set_flashdata('error', 'Failed to add new tenant: '.
												$e->getMessage());
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
		$data['available_themes'] = $this->get_available_themes();
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

	private function get_available_themes() {
		$available_themes = array();
		$all_themes = $this->vbx_theme->get_all();
		foreach ($all_themes as $theme) {
			$available_themes[$theme] = ucwords($theme);
		}
		return $available_themes;
	}
	
	/**
	 * We need to check for a few obscure parameters to see
	 * if we're gonna have problems with the GitHub API during
	 * the update check.
	 * 
	 * If either `safe_mode` or `open_basedir` are in effect then
	 * `CURLOPT_FOLLOWLOCATION` won't set as a curlopt. Since the 
	 * GitHub API uses this and also uses `curl_setopt_array` that
	 * means that if either of these are set then all of the curl
	 * settings fail to set.
	 * 
	 * This is a stop-gap measure until something more graceful
	 * can be implemented
	 *
	 * @return bool
	 */
	protected function can_check_upgrade()
	{
		$this->safe_mode = ini_get('safe_mode');
		$this->open_basedir = ini_get('open_basedir');
		
		$can_check = true;
		if ($this->safe_mode || strlen($this->open_basedir) > 0)
		{
			$can_check = false;
		}

		return $can_check;
	}
	
	/**
	 * Get the latest available tag from Github
	 * Latest tag == latest released version
	 * 
	 * Does not account for beta or alpha versions (which we haven't ever tagged)
	 *
	 * @return string
	 */
	public function get_latest_tag()
	{
		if ($cache = $this->api_cache->get('latest_version', 'Site', $this->tenant->id))
		{
			return $cache;
		}
		
		try {
			include_once(APPPATH . 'libraries/VBX_Github_Client.php');
			$gh = new VBX_Github_Client;			
			$tags = $gh->getTags();
						
			$latest = false;
					
			if (is_array($tags) && count($tags) > 0)
			{
				$list = array_keys($tags);
				usort($list, array($this, 'version_sort'));
				$latest = array_pop($list);
			}
								
			$this->api_cache->set('latest_version', $latest, 'Site', $this->tenant->id);
		}
		catch (Exception $e) {
			$latest = false;
			error_log('Could not check latest OpenVBX Version: '.$e->getMessage());
		}

		return $latest;
	}
	
	public function version_sort($a, $b) {
		return version_compare($a, $b, '<') ? -1 : version_compare($a, $b, '==') ? 0 : 1;
	}
}
