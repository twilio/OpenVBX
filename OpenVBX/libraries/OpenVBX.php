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

include(APPPATH.'libraries/Services/Twilio.php');

class OpenVBXException extends Exception {}
class OpenVBX {
	public static $currentPlugin = null;
	
	private static $_twilioService;
	private static $_twilioValidator;

	public static function query($sql)
	{
		return PluginData::sqlQuery($sql);
	}
	public static function one($sql)
	{
		return PluginData::one($sql);
	}

	public static function isAdmin() {
		$ci =& get_instance();
		$is_admin = $ci->session->userdata('is_admin');

		return ($is_admin == 1);
	}

	public static function getTwilioAccountType()
	{
		try
		{
			$ci =& get_instance();
			$ci->load->model('vbx_accounts');
			return $ci->vbx_accounts->getAccountType();
		}
		catch(VBX_AccountsException $e)
		{
			error_log($e->getMessage());
			self::setNotificationMessage($e->getMessage());
			return 'Full';
		}
	}

	public static function getCurrentUser()
	{
		$ci =& get_instance();
		$user_id = $ci->session->userdata('user_id');
		return VBX_User::get($user_id);
	}

	/**
	 * Get the twilio API version from the API endpoint settings
	 *
	 * @deprecated url versioning is handled by Twilio Services library
	 * @return mixes string/null
	 */
	public static function getTwilioApiVersion()
	{
		$ci =& get_instance();
		$url = $ci->vbx_settings->get('twilio_endpoint', VBX_PARENT_TENANT);
		if(preg_match('/.*\/([0-9]+-[0-9]+-[0-9]+)$/', $url, $matches))
		{
			return $matches[1];
		}

		return null;
	}

	public static function addCSS($file)
	{
		$ci =& get_instance();
		$plugin = OpenVBX::$currentPlugin;
		$info = $plugin->getInfo();
		$path = $info['plugin_path'] .'/'. $file;
		if(!is_file($path))
			error_log("Warning: CSS file does not exists: {$path}");
		$url = implode('/', array('plugins', $info['dir_name'], $file));
		$ci->template->add_css($url);
	}

	public static function addJS($file)
	{
		$ci =& get_instance();
		$plugin = OpenVBX::$currentPlugin;
		$info = $plugin->getInfo();
		$path = $info['plugin_path'] .'/'. $file;
		if(!is_file($path))
			error_log("Warning: JS script does not exists: {$path}");
		$url = implode('/', array('plugins', $info['dir_name'], $file));
		$ci->template->add_js($url);
	}

	public static function setNotificationMessage($message)
	{
		$ci =& get_instance();
		$ci->session->set_flashdata('error', $message);
	}

	public static function getUsers($options = array(), $limit = -1, $offset = 0)
	{
		return VBX_User::search($options, $limit, $offset);
	}

	public static function getGroups($options = array(), $limit = -1, $offset = 0)
	{
		return VBX_Group::search($options, $limit, $offset);
	}

	public static function getFlows($options = array(), $limit = -1, $offset = 0)
	{
		return VBX_Flow::search($options, $limit, $offset);
	}

	public static function addVoiceMessage($owner,
										   $sid,
										   $caller,
										   $called,
										   $recording_url,
										   $duration)
	{
		return self::addMessage($owner, $sid, $caller, $called, $recording_url,
								$duration, VBX_Message::TYPE_VOICE, null);
	}

	public static function addSmsMessage($owner,
										 $sid,
										 $to,
										 $from,
										 $body)
	{
		return self::addMessage($owner, $sid, $to, $from, '',
								0, VBX_Message::TYPE_SMS, $body, true);
	}

	public static function addMessage($owner,
									  $sid,
									  $caller,
									  $called,
									  $recording_url,
									  $duration,
									  $type = VBX_Message::TYPE_VOICE,
									  $text = null,
									  $notify = false)
	{
		try
		{
			$ci =& get_instance();
			$ci->load->model('vbx_message');
			if(!is_object($owner))
			{
				throw new VBX_MessageException('owner is invalid');
			}

			$owner_type = get_class($owner);
			$owner_type = str_replace('vbx_', '', strtolower($owner_type));
			$owner_id = $owner->id;


			$message = new VBX_Message();
			$message->owner_type = $owner_type;
			$message->owner_id = $owner_id;
			$message->call_sid = $sid;
			$message->caller = $caller;
			$message->called = $called;
			if(is_string($text))
			{
				$message->content_text = $text;
			}
			$message->content_url = $recording_url;
			$message->size = $duration;

			$message->type = $type;
			$message->status = VBX_Message::STATUS_NEW;

			return $ci->vbx_message->save($message, $notify);
		}
		catch(VBX_MessageException $e)
		{
			error_log($e->getMessage());
			return false;
		}
	}

	/* Returns the version from the php software on the server */
	public static function version()
	{
		$ci =& get_instance();
		$ci->load->model('vbx_settings');
		return $ci->vbx_settings->get('version', VBX_PARENT_TENANT);
	}

	/* Returns the version of the database schema */
	public static function schemaVersion()
	{
		$ci =& get_instance();
		$ci->load->model('vbx_settings');
		return $ci->vbx_settings->get('schema-version', VBX_PARENT_TENANT);
	}

	/* Returns the latest version of the schema on the server,
	 * regardless if its been imported */
	public static function getLatestSchemaVersion()
	{
		$updates = scandir(VBX_ROOT.'/updates/');
		foreach($updates as $i => $update)
		{
			$updates[$i] = intval(preg_replace('/.(sql|php)$/', '', $update));
		}

		sort($updates);
		return $updates[count($updates)-1];
	}

	public static function setPageTitle($title, $overwrite = false) 
	{
		$ci =& get_instance();
		return $ci->template->write('title', $title, $overwrite);
	}
	
	/**
	 * Get the Twilio Services Account object for communicating with Twilio HQ
	 * 
	 * Will return the proper account for communications with Twilio.
	 * This method is sub-account & twilio connect aware
	 * 
	 * Optional: Pass different Account Sid & Token values to communicate
	 * with a different Twilio Account
	 * 
	 * Twilio Connect Aware. Will return the connect account if applicable.
	 *
	 * @throws OpenVBXException if invalid parameters are passed in for new object generation
	 * @param string $twilio_sid Optional - Twilio Account Sid
	 * @param string $twilio_token Optional - Twilio Account Token
	 * @return object Services_Twilio_Rest_Account
	 */
	public static function getAccount($twilio_sid = false, $twilio_token = false, $api_version = '2010-04-01') 
	{
		$_http = null;
		$ci =& get_instance();
		
		// internal api development override, you'll never need this
		if ($_http_settings = $ci->config->item('_http_settings')) 
		{
			if (!empty($_http_settings['host'])) 
			{
				$_http = new Services_Twilio_TinyHttp(
				                $_http_settings['host'],
				                array("curlopts" => array(CURLOPT_USERAGENT => Services_Twilio::USER_AGENT))
				            );
			}
		}
		
		// if sid & token are passed, make sure they're not the same as our master
		// values. If they are, make a new object, otherwise use the same internal object
		if (!empty($twilio_sid) || !empty($twilio_token)) 
		{
			if (!empty($twilio_sid) && !empty($twilio_token)) 
			{
				if ($twilio_sid != $ci->twilio_sid && $twilio_token != $ci->twilio_token) 
				{
					try {
						$service = new Services_Twilio(
												$twilio_sid, 
												$twilio_token,
												$api_version,
												$_http
											);
						return $service->account;
					}
					catch (Exception $e) {
						throw new OpenVBXException($e->getMessage());
					}
				}
			}
			else 
			{
				throw new OpenVBXException('Both a Sid & Token are required to get a new Services Object');
			}
		}

		// return standard service object
		if (!(self::$_twilioService instanceof Services_Twilio)) 
		{	
			try {
				self::$_twilioService = new Services_Twilio(
													$ci->twilio_sid, 
													$ci->twilio_token,
													$api_version,
													$_http
												);
			}
			catch (Exception $e) {
				throw new OpenVBXException($e->getMessage());
			}
		}
		
		return self::$_twilioService->account;
	}
	
	public function getAccounts() {
		if (!(self::$_twilioService instanceof Services_Twilio)) {
			$ci =& get_instance();
			self::getAccount();
		}
		return self::$_twilioService->accounts;
	}
	
	/**
	 * Validate that the current request came from Twilio
	 * 
	 * If no url is passed then the default $_SERVER['REQUEST_URI'] will be passed
	 * through site_url().
	 * 
	 * If no post_vars are passed then $_POST will be used directly.
	 *
	 * @param string $uri 
	 * @param array $post_vars 
	 * @return bool
	 */
	public static function validateRequest($url = false, $post_vars = false) 
	{
		$ci =& get_instance();
		if ($ci->tenant->type == VBX_Settings::AUTH_TYPE_CONNECT) {
			return true;
		}
		
		if (!(self::$_twilioValidator instanceof Services_Twilio_RequestValidator)) 
		{
			$ci =& get_instance();
			self::$_twilioValidator = new Services_Twilio_RequestValidator($ci->twilio_token);
		}
		
		if (empty($url)) 
		{
			// we weren't handed a uri, use the default
			$url = site_url($ci->uri->uri_string());
		}
		elseif (strpos($url, '://') === false) 
		{
			// we were handed a relative uri, make it full
			$url = site_url($url);
		}
	
		if (empty($post_vars)) 
		{
			// we weren't handed post-vars, use the default
			$post_vars = $_POST;
		}
		
		return self::$_twilioValidator->validate(self::getRequestSignature(), $url, $post_vars);
	}
	
	/**
	 * Get the X-Twilio-Signature header value
	 *
	 * @todo maybe needs some special love for nginx?
	 * @return mixed string, boolean false if not found
	 */
	public static function getRequestSignature() 
	{
		$request_signature = false;
		if (!empty($_SERVER['HTTP_X_TWILIO_SIGNATURE'])) 
		{
			$request_signature = $_SERVER['HTTP_X_TWILIO_SIGNATURE'];
		}
		return $request_signature;
	}
	
	public function connectAuthTenant($tenant_id) {
		$auth = true;
				
		$ci =& get_instance();
		$tenant = $ci->db->get_where('tenants', array('id' => $tenant_id))->result();
										
		if ($tenant && $tenant[0]->id == $tenant_id) 
		{
			try {
				if ($tenant->type == VBX_Settings::AUTH_TYPE_CONNECT) 
				{
					$sid = $ci->db->get_where('settings', array(
										'name' => 'twilio_sid',
										'tenant_id' => $tenant->id
									));
					$token = $ci->db->get_where('settings', array(
										'name' => 'twilio_token',
										'tenant_id' => VBX_PARENT_TENANT
									));
				}
				$account = self::getAccount($sid, $token);
				$account_type = $account->type;
			}
			catch (Exception $e) {
				// @todo - check for 20006 code, currently returns 20003
				log_message('Connect auth failed: '.$e->getMessage().' :: '.$e->getCode());
				$auth = false;
			}
		}
		
		return $auth;
	}
}
