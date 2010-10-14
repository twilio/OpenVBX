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

require_once 'GoogleUtilityClient.php';

class GoogleDomainException extends Exception {}
class GoogleCaptchaChallengeException extends Exception {
	public $captcha_url;
	public $captcha_token;
}

class GoogleDomain
{
	public static function authenticate($api_key, $api_secret, $captcha, $captcha_token)
	{
		try
		{
			$api = new GoogleUtilityClient($api_key,
										   $api_secret,
										   'HOSTED_OR_GOOGLE',
										   $captcha,
										   $captcha_token);
			$api->authenticate();

			return TRUE;
		}		
		catch(GoogleUtilityClientException $e)
		{
			switch(GoogleLoginChallenge::get_error($e->getCode())) {
				case 'CaptchaRequired':
					$captchaException = new GoogleCaptchaChallengeException($e->getMessage(),
																			$e->getCode());
					$captchaException->captcha_url = $api->auth_response['CaptchaUrl'];
					$captchaException->captcha_token = $api->auth_response['CaptchaToken'];
					throw $captchaException;
			}

			throw new GoogleDomainException($e->getMessage(), $e->getCode());
		}
	}
	
	public static function get_users($api_key, $api_secret)
	{
		try
		{
			$api = new GoogleUtilityClient($api_key,
										   $api_secret,
										   'HOSTED_OR_GOOGLE');

			$api->authenticate();
			$user_response = $api->get($api->domain . '/user/2.0');
			
			$titles = $user_response['list']
				 ->getElementsByTagName('title');
			$users = array();
			foreach($titles as $title)
			{
				if($title->parentNode->nodeName == 'entry') {
					$users[] = $title->textContent . '@'. $api->domain;
				}
			}
			
		}
		catch(GoogleUtilityClientException $e)
		{
			throw new GoogleDomainException($e->getMessage());
		}
		
		return $users;
	}

	public static function get_groups($api_key, $api_secret)
	{
		try
		{
			$api = new GoogleUtilityClient($api_key,
										   $api_secret,
										   'HOSTED_OR_GOOGLE');
			$api->authenticate();
			
			$groups_response = $api->get('group/2.0/' . $api->domain);
			$entries = $groups_response['list']->getElementsByTagName('property');
			$groups = array();
			foreach($entries as $entry)
			{
				if($entry->getAttribute('name') == 'groupName') {
					$groups[] = $entry->getAttribute('value');
				}
			}
		}
		catch(GoogleUtilityClientException $e)
		{
			throw new GoogleDomainException($e->getMessage());
		}
		
		return $groups;
	}

}
