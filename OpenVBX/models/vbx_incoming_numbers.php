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
		$this->cache_key = $this->twilio_sid . '_incoming_numbers';

	}

	public function get_sandbox()
	{
		if(function_exists('apc_fetch')) 
		{
			$success = FALSE;
			$cachekey = $this->cache_key.'sandbox';
			$sandbox = apc_fetch($cachekey, $success);

			if($sandbox AND $success) 
			{
				return @unserialize($sandbox);
			}
		}

		try {
			$account = OpenVBX::getAccount();
			$sandbox = $account->sandbox;
			if (!empty($sandbox) && ($sandbox instanceof Services_Twilio_Rest_Sandbox)) 
			{
				$sandbox = $this->parseIncomingPhoneNumber($sandbox);
				if (function_exists('apc_store')) 
				{
					$success = apc_store($cachekey, serialize($sandbox), self::CACHE_TIME_SEC);
				}
			}
		}
		catch (Exception $e) {
			throw new VBX_IncomingNumberException($e->getMessage);
		}

		return $sandbox;
	}

	public function get_numbers($retrieve_sandbox = true)
	{
		if(function_exists('apc_fetch')) 
		{
			$success = FALSE;
			$cachekey = $this->cache_key.'numbers'.$retrieve_sandbox;
			$data = apc_fetch($cachekey, $success);
			if($data AND $success) 
			{
				$numbers = @unserialize($data);
				if(is_array($numbers))
				{
					return $numbers;
				}
			}
		}

		$numbers = array();
		try {
			$account = OpenVBX::getAccount();
			foreach ($account->incoming_phone_numbers as $number) 
			{
				// check that number is a proper instance type
				$numbers[] = $this->parseIncomingPhoneNumber($number);
			}
		}
		catch (Exception $e) {
			throw new VBX_IncomingNumberException($e->getMessage());
		}
		
		$ci = &get_instance();
		$enabled_sandbox_number = $ci->settings->get('enable_sandbox_number', $ci->tenant->id);
		if ($enabled_sandbox_number && $retrieve_sandbox) 
		{
			$numbers[] = $this->get_sandbox();
		}

		if(function_exists('apc_store')) 
		{
			$success = apc_store($cachekey, serialize($numbers), self::CACHE_TIME_SEC);
		}

		return $numbers;
	}
	
	public function get_available_countries()
	{
		if (function_exists('apc_fetch'))
		{
			$success = FALSE;
			$data = apc_fetch($this->cache_key.'countries', $success);
			if ($data AND $success)
			{
				$countries = @unserialize($data);
				if (is_array($countries))
				{
					return $countries;
				}
			}
		}
		
		$countries = array();
		$ci =& get_instance();
		$ci->config->load('countrycodes');
		
		try {
			$account = OpenVBX::getAccount();
			$page = 0;
			do {
				$list = $account->available_phone_numbers->getPage($page);
				if (is_array($list->countries) && count($list->countries)) 
				{
					foreach ($list->countries as $country)
					{
						// no subresource uris means the account can't purchase here
						// or that the country is not yet available for purchase
						if (empty($country->subresource_uris))
						{
							continue;
						}
						
						if ($countrydata = $ci->config->item($country->country_code,'countrycodes'))
						{
							$country->code = $countrydata[0];
							if (!empty($countrydata[1]))
							{
								$country->search = $countrydata[1];
							}
							else
							{
								$country->search = '+'.$country->code.' (*)';
							}
						}
						$countries[$country->country_code] = $country;
					}
				}
				$page++;
			}
			while (!empty($list->next_page_uri));
		}
		catch (Exception $e) {
			throw new VBX_IncomingNumberException($e->getMessage());
		}

		ksort($countries);

		if (function_exists('apc_store'))
		{
			apc_store($this->cache_key.'countries', serialize($countries));
		}

		return $countries;
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
		$num->id = $item->sid ? $item->sid : 'Sandbox';
		$num->name = $item->friendly_name;
		$num->phone = format_phone($item->phone_number);
		$num->pin = $item->pin ? $item->pin : null;
		$num->sandbox = $item->pin ? true : false;
		$num->url = $item->voice_url;
		$num->method = $item->voice_method;
		$num->smsUrl = $item->sms_url;
		$num->smsMethod = $item->sms_method;
		$num->capabilities = $item->capabilities;
		$num->voiceApplicationSid = $item->voice_application_sid;

		// @todo do comparison against url domain, then against 'twiml/start'
		// then include warning when small differences like www/non-www are encountered
		// don't be friendly to other sub-domain matches, only www since that is the
		// only safe variation to assume
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

	/**
	 * Assign a number to a flow
	 *
	 * @param string $phone_id - phone number sid
	 * @param int $flow_id - flow id
	 * @return bool
	 */
	public function assign_flow($phone_id, $flow_id)
	{
		$voice_url = site_url('twiml/start/voice/'.$flow_id);
		$sms_url = site_url('twiml/start/sms/'.$flow_id);

		try {
			$account = OpenVBX::getAccount();
			if (strtolower($phone_id) == 'sandbox') 
			{
				$number = $account->sandbox;
			}
			else {
				$number = $account->incoming_phone_numbers->get($phone_id);
			}

			$number->update(array(
					'VoiceUrl' => $voice_url,
					'SmsUrl' => $sms_url,
					'VoiceFallbackUrl' => base_url().'fallback/voice.php',
					'SmsFallbackUrl' => base_url().'fallback/sms.php',
					'VoiceFallbackMethod' => 'GET',
					'SmsFallbackMethod' => 'GET',
					'SmsMethod' => 'POST',
					'ApiVersion' => '2010-04-01'
				));
		} 
		catch (Exception $e) 
		{
			throw new VBX_IncomingNumberException($e->getMessage());
		}

		$this->clear_cache();
		return TRUE;
	}

	/**
	 * Purchase a new number
	 *
	 * @param bool $is_local 
	 * @param string $area_code 
	 * @return void
	 */
	public function add_number($is_local, $area_code, $country)
	{		
		$voice_url = site_url("twiml/start/voice/0");
		$sms_url = site_url("twiml/start/sms/0");

		if($is_local
		   && (
			   !empty($area_code) &&
				preg_match('/([^0-9])/', $area_code) > 0))
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
		try {
			$account = OpenVBX::getAccount();
			// purchase tollfree, uses AvailablePhoneNumbers to search first.
			if(!$is_local) 
			{
				$numbers = $account->available_phone_numbers
													->getTollFree($country)
													->getList();
				
				if (count($numbers->available_phone_numbers)) 
				{
					$params['PhoneNumber'] = $numbers->available_phone_numbers[0]->phone_number;
				}
				else 
				{
					throw new VBX_IncomingNumberException('Currently out of TollFree numbers. '.
															'Please try again later.');
				}
			}
			else 
			{ 
				$search_params = array();
				if (!empty($area_code))
				{
					$search_params['AreaCode'] = $area_code;
				}
				$numbers = $account->available_phone_numbers
													->getList($country, 'Local', $search_params);

				if (count($numbers->available_phone_numbers))
				{
					$params['PhoneNumber'] = $numbers->available_phone_numbers[0]->phone_number;
				}
				else
				{
					if (!empty($area_code))
					{
						$message = 'Could not find any numbers in Area Code "'.$area_code.'". '.
								'Please try again later or try a different Area Code.';
					}
					else 
					{
						$message = 'Could not find any available phone numbers. '.
								'Please try again later.';
					}
					throw new VBX_IncomingNumberException($message);
				}
			}
			$number = $account->incoming_phone_numbers->create($params);
		}
		catch (Exception $e) 
		{
			throw new VBX_IncomingNumberException($e->getMessage());
		}

		$this->clear_cache();
		return $this->parseIncomingPhoneNumber($number);
	}

	/**
	 * Remove a phone number from the current account
	 *
	 * @param string $phone_id
	 * @return bool
	 */
	public function delete_number($phone_id)
	{
		try {
			$account = OpenVBX::getAccount();
			$account->incoming_phone_numbers->delete($phone_id);
		}
		catch (Exception $e) 
		{
			throw new VBX_IncomingNumberException($e->getMessage());
		}
	
		$this->clear_cache();
		return TRUE;
	}

}
