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

require_once(APPPATH . 'libraries/twilio.php');

class VBX_IncomingNumberException extends Exception {}

class VBX_Incoming_numbers extends Model
{
	private $cache_key;

	const CACHE_TIME_SEC = 3600;

	public function __construct()
	{
		parent::__construct();

		$this->twilio = new TwilioRestClient($this->twilio_sid,
											 $this->twilio_token,
											 $this->twilio_endpoint);

		$this->cache_key = $this->twilio_sid . '_incoming_numbers';

	}

	function get_sandbox()
	{
		if(function_exists('apc_fetch')) {
			$success = FALSE;
			$data = apc_fetch($this->cache_key.'sandbox', $success);

			if($data AND $success) {
				$sandbox = simplexml_load_string($data);
				return $sandbox;
			}
		}

		/* Get Sandbox Number */
		$sandbox = FALSE;
		try
		{
			$response = $this->twilio->request("Accounts/{$this->twilio_sid}/Sandbox");
		}
		catch(TwilioException $e)
		{
			throw new VBX_IncomingNumberException('Failed to connect to Twilio.', 503);
		}

		if(isset($response->ResponseXml->TwilioSandbox))
		{
			$sandbox = $response->ResponseXml->TwilioSandbox;
		}
		
		if($sandbox instanceof SimpleXMLElement && function_exists('apc_store')) {
			$success = apc_store($this->cache_key.'sandbox', $sandbox->asXML(), self::CACHE_TIME_SEC);
		}

		return $sandbox;
	}

	function get_numbers($retrieve_sandbox = true)
	{
		$numbers = array();
		if(function_exists('apc_fetch')) {
			$success = FALSE;
			$data = apc_fetch($this->cache_key.'numbers'.$retrieve_sandbox, $success);
			if($data AND $success) {
				$numbers = @unserialize($data);
				if(is_array($numbers)) return $numbers;
			}
		}

		$items = array();
		$nextpageuri = "Accounts/{$this->twilio_sid}/IncomingPhoneNumbers";
		do {
			/* Get IncomingNumbers */
			try
			{
				$response = $this->twilio->request($nextpageuri);
			}
			catch(TwilioException $e)
			{
				throw new VBX_IncomingNumberException('Failed to connect to Twilio.', 503);
			}

			if($response->IsError)
			{
				throw new VBX_IncomingNumberException($response->ErrorMessage, $response->HttpStatus);
			}

			if(isset($response->ResponseXml->IncomingPhoneNumbers->IncomingPhoneNumber))
			{
				$phoneNumbers = $response->ResponseXml->IncomingPhoneNumbers->IncomingPhoneNumber
					? $response->ResponseXml->IncomingPhoneNumbers->IncomingPhoneNumber
					: array($response->ResponseXml->IncomingPhoneNumbers->IncomingPhoneNumber);
				foreach($phoneNumbers as $number)
				{
					$items[] = $number;
				}
			}
			
			$nextpageuri = (string) $response->ResponseXml->IncomingPhoneNumbers['nextpageuri'];
			$nextpageuri = preg_replace('|^/\d{4}-\d{2}-\d{2}/|m', '', $nextpageuri);
		} while (!empty($nextpageuri));
		
		$ci = &get_instance();
		$enabled_sandbox_number = $ci->settings->get('enable_sandbox_number', $ci->tenant->id);
		if($enabled_sandbox_number && $retrieve_sandbox) {
			$sandbox = $this->get_sandbox();
			if (!empty($sandbox)) {
				$items[] = $sandbox;
			}
		}

		foreach($items as $item)
		{
			$numbers[] = $this->parseIncomingPhoneNumber($item);
		}

		if(function_exists('apc_store')) {
			$success = apc_store($this->cache_key.'numbers'.$retrieve_sandbox, serialize($numbers), self::CACHE_TIME_SEC);
		}

		return $numbers;
	}

	private function clear_cache()
	{
		if(function_exists('apc_delete'))
		{
			apc_delete($this->cache_key.'numbers');
			apc_delete($this->cache_key.'numbers1');
			apc_delete($this->cache_key.'numbers0');
			apc_delete($this->cache_key.'sandbox');
			apc_delete($this->cache_key.'sandbox1');
			apc_delete($this->cache_key.'sandbox0');
			return TRUE;
		}

		return FALSE;
	}

	private function parseIncomingPhoneNumber($item)
	{
		$num = new stdClass();
		$num->flow_id = null;
		$num->id = (string) isset($item->Sid)? (string) $item->Sid : 'Sandbox';
		$num->name = (string) $item->FriendlyName;
		$num->phone = format_phone($item->PhoneNumber);
		$num->pin = isset($item->Pin)? (string)$item->Pin : null;
		$num->sandbox = isset($item->Pin)? true : false;
		$num->url = (string) $item->VoiceUrl;
		$num->method = (string) $item->VoiceMethod;
		$num->smsUrl = (string) $item->SmsUrl;
		$num->smsMethod = (string) $item->SmsMethod;

		$call_base = site_url('twiml/start') . '/';
		$base_pos = strpos($num->url, $call_base);
		$num->installed = ($base_pos !== FALSE);

		$matches = array();

		if (!preg_match('/\/(voice|sms)\/(\d+)$/', $num->url, $matches) == 0)
		{
			$num->flow_id = intval($matches[2]);
		}

		return $num;
	}

	function assign_flow($phone_id, $flow_id)
	{
		$voice_url = site_url("twiml/start/voice/$flow_id");
		$sms_url = site_url("twiml/start/sms/$flow_id");
		if(strtolower($phone_id) == 'sandbox')
			$rest_url = "Accounts/{$this->twilio_sid}/Sandbox";
		else
			$rest_url = "Accounts/{$this->twilio_sid}/IncomingPhoneNumbers/$phone_id";

		$response = $this->twilio->request($rest_url,
										   'POST',
										   array('VoiceUrl' => $voice_url,
												 'SmsUrl' => $sms_url,
												 'VoiceFallbackUrl' => base_url().'fallback/voice.php',
												 'SmsFallbackUrl' => base_url().'fallback/sms.php',
												 'VoiceFallbackMethod' => 'GET',
												 'SmsFallbackMethod' => 'GET',
												 'SmsMethod' => 'POST',
												 'ApiVersion' => '2010-04-01',
												 )
										   );

		if($response->IsError)
		{
			throw new VBX_IncomingNumberException($response->ErrorMessage);
		}

		$this->clear_cache();
		return TRUE;
	}

	// purchase a new phone number, return the new number
	function add_number($is_local, $area_code)
	{
		$voice_url = site_url("twiml/start/voice/0");
		$sms_url = site_url("twiml/start/sms/0");

		if($is_local
		   && (
			   !empty($area_code) &&
			   (strlen(trim($area_code)) != 3 ||
				preg_match('/([^0-9])/', $area_code) > 0)))
		{
			throw new VBX_IncomingNumberException('Area code invalid');
		}

		$params =
			 array('VoiceUrl' => $voice_url,
				   'SmsUrl' => $sms_url,
				   'VoiceFallbackUrl' => base_url().'fallback/voice.php',
				   'SmsFallbackUrl' => base_url().'fallback/sms.php',
				   'VoiceFallbackMethod' => 'GET',
				   'SmsFallbackMethod' => 'GET',
				   'SmsMethod' => 'POST',
				   'ApiVersion' => '2010-04-01',
				   );

		// purchase tollfree, uses AvailablePhoneNumbers to search first.
		if(!$is_local) {
			$response = $this->twilio->request("Accounts/{$this->twilio_sid}/AvailablePhoneNumbers/US/TollFree");
			if($response->IsError)
				throw new VBX_IncomingNumberException($response->ErrorMessage);

			$availablePhoneNumbers = $response->ResponseXml->AvailablePhoneNumbers;
			if(empty($availablePhoneNumbers->AvailablePhoneNumber))
				throw new VBX_IncomingNumberException("Currently out of TollFree numbers, please try again later.");

			// Grab the first number from the list.
			$params['PhoneNumber'] = $availablePhoneNumbers->AvailablePhoneNumber->PhoneNumber;
			$response = $this->twilio->request("Accounts/{$this->twilio_sid}/IncomingPhoneNumbers",
											   'POST',
											   $params );

		}  else { // purchase local

			if(!empty($area_code))
			{
				$params['AreaCode'] = $area_code;
			}

			$rest_url = "Accounts/{$this->twilio_sid}/IncomingPhoneNumbers/";
			$response = $this->twilio->request($rest_url, 'POST', $params);
		}

		if($response->IsError)
			throw new VBX_IncomingNumberException($response->ErrorMessage);

		$this->clear_cache();

		return $this->parseIncomingPhoneNumber($response->ResponseXml->IncomingPhoneNumber);
	}

	// purchase a new phone number, return the new number
	function delete_number($phone_id)
	{
		$rest_url = "Accounts/{$this->twilio_sid}/IncomingPhoneNumbers/$phone_id";

		$response = $this->twilio->request($rest_url, 'DELETE');

		if($response->IsError) throw new VBX_IncomingNumberException($response->ErrorMessage);

		$this->clear_cache();

		return TRUE;
	}

}
