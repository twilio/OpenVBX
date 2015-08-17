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

if (!function_exists('generate_capability_token')) {
	/**
	 * Generate a capability token for Twilio Client
	 *
	 * @param string|bool $allow_incoming
	 * @return string
	 */
	function generate_capability_token($allow_incoming = true) {
		$ci =& get_instance();
		$capability = new Services_Twilio_Capability($ci->twilio_sid, $ci->twilio_token);

		$user_id = intval($ci->session->userdata('user_id'));
		$user = VBX_user::get(array('id' => $user_id));

		$params = array(
			'user_id' => $user->user_id,
		);
		
		$token = null;
		try {
			$capability->allowClientOutgoing($ci->application_sid, $params);
			if ($allow_incoming) {
				$capability->allowClientIncoming($user->id);
			}
			$token = $capability->generateToken(VBX_Settings::CLIENT_TOKEN_TIMEOUT);
		}
		catch (Exception $e) {
			log_message('error', $e->getMessage());
		}
		
		return $token;
	}
}

if (!function_exists('validate_rest_request')) {
	/**
	 * Validate that an incoming rest request is from Twilio
	 *
	 * @param string $failure_message
	 * @return void
	 */
	function validate_rest_request($failure_message = 'Could not validate this request. Goodbye.') {
		$ci =& get_instance();
		if ($ci->tenant->type == VBX_Settings::AUTH_TYPE_CONNECT)
		{
			return;
		}
		
		if (!OpenVBX::validateRequest()) {
			$response = new TwimlResponse;
			$response->say($failure_message, array(
					'voice' => $ci->vbx_settings->get('voice', $ci->tenant->id),
					'language' => $ci->vbx_settings->get('voice_language', $ci->tenant->id)
				));
			$response->hangup();
			$response->respond();
			exit;
		}
	}
}

if (!function_exists('clean_digits')) {
	/**
	 * Cleans Hash and Star characters from returned Digits
	 * If digits only contains Hash or Star characters then
	 * the raw digits param is returned
	 *
	 * @param string $digits 
	 * @return string
	 */
	function clean_digits($digits) {
		$trimmed = str_replace(array('#', '*'), '',  $digits);
		return strlen($trimmed) > 0 ? $trimmed : $digits;
	}
}

if (!function_exists('process_device_for_number'))
{
	function process_device_for_number($caller_id)
	{
		if (preg_match('|device:[0-9]{1,3}|', $caller_id))
		{
			$device_id = str_replace('device:', '', $caller_id);
			$device = VBX_Device::get(array('id' => $device_id));
			if ($device instanceof VBX_Device)
			{
				$caller_id = $device->value;
			}
		}

		return $caller_id;
	}
}

if (!function_exists('version_url')) {
	/**
	 * Append the current site rev to a url to force asset reload on upgrade/change
	 *
	 * @param string $url
	 * @return string
	 */
	function version_url($url) {
		if (strpos($url, 'v=') === false)
		{
			$ci =& get_instance();
			$vers = 'v='.$ci->config->item('site_rev');
			$pre = (strpos($url, '?') === false ? '?' : '&amp;'); 
			$url .= $pre.$vers;
		}
		return $url;
	}
}

if (!function_exists('_deprecated_notice')) {
	/**
	 * Throw a deprecated method warning
	 * Allows backwards compatibility, but with a nag
	 *
	 * @param string $method 
	 * @param float $version 
	 * @param string $replacement optional, but recommended
	 * @return void
	 */
	function _deprecated_notice($method, $version, $replacement = null) {
		if (!is_null($replacement)) {
			$message = sprintf('`%s` is deprecated since version %f. Use `%s` instead.', $method, $version, $replacement);
		}
		else {
			$message = sprintf('`%s` is deprecated since version %f.', $method, $version);
		}
		log_message('error', $message);
		trigger_error($message, E_USER_WARNING);
	}
}

if (!function_exists('t_form_dropdown')) {
    /**
     * An easier to use version of form_dropdown
     *
     * @param array $params
     * @param array $options
     * @param bool|string $selected
     * @return string HTML
     */
	function t_form_dropdown($params, $options, $selected = false)
	{
		$name = '';
		if (!empty($params['name']))
		{
			$name = $params['name'];
		}
		
		$extra = '';
		foreach (t_form_valid_extra_attributes() as $key) 
		{
			if (!empty($params[$key])) 
			{
				$extra .= ' '.$key.'="'.$params[$key].'"';
			}
		}
		
		return form_dropdown($name, $options, $selected, $extra);
	}
}

if (!function_exists('t_form_input'))
{
	function t_form_input($params, $value = '')
	{
        $data = array();

		if (!empty($params['name']))
		{
			$data['name'] = $params['name'];
		}
		
		$extra = '';
		foreach (t_form_valid_extra_attributes() as $key)
		{
			if (!empty($params[$key]))
			{
				$extra .= ' '.$key.'="'.$params[$key].'"';
			}
		}

		return form_input($data, $value, $extra);
	}
}

if (!function_exists('t_form_button'))
{
	function t_form_button($params)
	{
		if (!empty($params['name']))
		{
			$data = array(
				'name' => $params['name'],
			);
		}
		
		$content = $params['value'];
		
		$extra = '';
		foreach (t_form_valid_extra_attributes() as $key)
		{
			if (!empty($params[$key]))
			{
				$extra .= ' '.$key.'="'.$params[$key].'"';
			}
		}
		
		if (isset($params['type']) && $params['type'] == 'submit')
		{
			return form_submit($data, $content, $extra);
		}
		else
		{
			return form_button($data, $content, $extra);
		}
	}
}

if (!function_exists('t_form_valid_attributes'))
{
	/**
	 * Since CodeIgniter insists on setting id, class, etc... as 
	 * extra attributes, we'll help ourselves out with a list of
	 * valid extras per type
	 *
	 * @return array
	 */
	function t_form_valid_extra_attributes()
	{
		return array('id', 'class', 'tabindex',
			'disabled',	'readonly', 'placeholder',
			'src', 'size', 'maxlength',
			'alt', 'accept'
		);
	}
}

if (!function_exists('gravatar_url'))
{
	function gravatar_url($email, $size = 30, $default_image)
	{
		$url = (is_ssl() ? 'https://secure' : 'http://www').
				'.gravatar.com/avatar/'.
				md5(strtolower(trim($email))).
				'?s='.intval($size).
				'&amp;d='.urlencode($default_image).
				'&amp;r=pg';
				
		return $url;
	}
}

if (!function_exists('is_ssl'))
{
	function is_ssl() {
		return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? true : false;
	}
}

if (!function_exists('flush_minify_caches'))
{
	function flush_minify_caches()
	{
		// flush minify caches on save
		try {
			$minpath = realpath(dirname(APPPATH)).'/assets/min/';
			require_once($minpath.'lib/Solar/Dir.php');
			$tmppath = rtrim(Solar_Dir::tmp(), DIRECTORY_SEPARATOR);
			
			$files = glob(rtrim($tmppath, '/').'/minify_*');
			foreach ($files as $file)
			{
				if (is_writable($file))
				{
					unlink($file);
				}
			}
		}
		catch (Exception $e) {
			log_message('error', $e->getMessage());
		}
	}
}

if (!function_exists('set_last_known_url'))
{
	function set_last_known_url($url, $expires = 0)
	{
		// setcookie('last_known_url', $url, intval($expires), $path);
		setcookie('last_known_url', $url, intval($expires), '/');
	}
}
