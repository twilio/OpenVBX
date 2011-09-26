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
	
class ErrorMessages
{
	public static $twilio_api = array(
									  404 => 'Twilio authentication error.	Is your account <a href="http://www.twilio.com/user/account/">active?</a>.',
									  401 => 'Twilio authentication error.	Does account information match your <a href="http://www.twilio.com/user/account/">twilio account?</a>.',
									  400 => 'Twilio authentication error.	Please contact your OpenVBX provider',
									  500 => 'Twilio authentication error.	Please contact <a href="http://www.twilio.com/user/account">twilio</a>',
									  503 => 'Twilio service unavailable due to network connectivity issues.',
									  /** Updated, Disruptive Technologies, for Tropo VBX conversion **/
									  999 => 'Tropo connection error. Does your account information match your tropo account?',
									  /** End Disruptive Technologies code **/
									  );

	public static function message($group, $code)
	{
		$message = null;
		$error_group = null;
		
		if(property_exists('ErrorMessages', $group))
		{
			$error_group = self::$$group;
		}
		
		if(is_array($error_group))
		{
			$message = isset($error_group[$code])? $error_group[$code] : "Unknown Error: $code";
		}
		
		if(is_null($message))
		{
			$message = 'Unknown Error';
		}

		return $message;
	}
}