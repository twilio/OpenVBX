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
			$response->respond();
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

?>