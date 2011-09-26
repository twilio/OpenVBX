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
	 * @param string $allow_incoming 
	 * @return string
	 */
	function generate_capability_token($rest_access, $allow_incoming = true) {
		$ci =& get_instance();
		$capability = new Services_Twilio_Capability($ci->twilio_sid, $ci->twilio_token);

		$user_id = intval($ci->session->userdata('user_id'));
		$user = VBX_user::get(array('id' => $user_id));

		$params = array(
			'user_id' => $user->user_id,
			'rest_access' => $rest_access
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
			error_log($e->getMessage());
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
		if (!OpenVBX::validateRequest()) {
			$response = new TwimlResponse;
			$response->say($failure_message);
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
			$pre = (strpos($url, '?') === false ? '?' : '&'); 
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
			$message = sprintf('`%s` is deprecated since version %f. Use `%s` instead.');
		}
		else {
			$message = sprintf('`%s` is deprecated since version %f.');			
		}
		trigger_error($message, E_WARNING);
	}
}

if (!function_exists('t_form_dropdown')) {
	/**
	 * An easier to use version of form_dropdown
	 *
	 * @param array $params 
	 * @param array $options 
	 * @param string $selected 
	 * @return string HTML
	 */
	function t_form_dropdown($params, $options, $selected = false)
	{
		$name = $params['name'];
		
		$extra = '';
		foreach (array('id', 'class', 'tabindex') as $key) {
			if (!empty($params[$key])) {
				$extra .= ' '.$key.'="'.$params[$key].'"';
			}
		}
		
		return form_dropdown($name, $options, $selected, $extra);
	}
}

?>