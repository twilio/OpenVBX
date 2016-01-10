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
	public static $areaCodeCountries = array(
		'US',
		'CA',
	);
	
	public function __construct()
	{
		parent::__construct();
	}

	public function get_numbers($retrieve_sandbox = 'deprecated')
	{		
		$ci =& get_instance();
		$cache_key = 'incoming-numbers';
		if ($cache = $ci->api_cache->get($cache_key, __CLASS__, $ci->tenant->id))
		{
			return $cache;
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
			$msg = 'Unable to fetch Numbers: ';
			switch ($e->getCode())
			{
				case 20003:
					$msg .= 'Authentication Failed.';
					break;
				default:
					$msg .= $e->getMessage();
			}
			throw new VBX_IncomingNumberException($msg, $e->getCode());
		}

		$ci->api_cache->set('incoming-numbers', $numbers, __CLASS__, $ci->tenant->id);

		return $numbers;
	}
	
	public function get_available_countries()
	{	
		$ci =& get_instance();
		if ($cache = $ci->api_cache->get('countries', __CLASS__, $ci->tenant->id))
		{
			return $cache;
		}

		$countries = array();		
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
						
						// we'll not need this again
						unset($country->client);
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
		$ci->api_cache->set('countries', $countries, __CLASS__, $ci->tenant->id);

		return $countries;
	}

	/**
	 * @param $item
	 * @return stdClass
	 */
	private function parseIncomingPhoneNumber($item)
	{
		$num = new stdClass();
		$num->flow_id = null;
		$num->id = $item->sid;
		$num->name = $item->friendly_name;
		$num->phone = format_phone($item->phone_number);
		$num->phone_number = $item->phone_number;
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
		if ($num->installed && preg_match('/\/(voice|sms)\/(\d+)$/', $num->url, $matches) > 0)
		{
			$num->flow_id = intval($matches[2]);
		}

		return $num;
	}

	/**
	 * Assign a number to a flow
	 *
	 * @throws VBX_IncomingNumberException
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
			/** @var Services_Twilio_Rest_IncomingPhoneNumber $number */
			$number = $account->incoming_phone_numbers->get($phone_id);

			$number->update(array(
					'VoiceUrl' => $voice_url,
					'SmsUrl' => $sms_url,
					'VoiceFallbackUrl' => base_url().'fallback/voice.php',
					'SmsFallbackUrl' => base_url().'fallback/sms.php',
					'VoiceFallbackMethod' => 'GET',
					'SmsFallbackMethod' => 'GET',
					'SmsMethod' => 'POST',
					'ApiVersion' => '2010-04-01',
					'VoiceApplicationSid' => '',
					'SmsApplicationSid' => ''
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
	 * @throws VBX_IncomingNumberException
	 * @param bool $is_local 
	 * @param string $area_code
	 * @param string $country
	 * @return stdClass
	 */
	public function add_number($is_local, $area_code, $country)
	{		
		$voice_url = site_url("twiml/start/voice/0");
		$sms_url = site_url("twiml/start/sms/0");

		if($is_local && (!empty($area_code) && preg_match('/([^0-9])/', $area_code) > 0))
		{
			throw new VBX_IncomingNumberException('Area code invalid');
		}

		$params = array(
			'VoiceUrl' => $voice_url,
			'SmsUrl' => $sms_url,
			'VoiceFallbackUrl' => $this->base_url().'fallback/voice.php',
			'SmsFallbackUrl' => $this->base_url().'fallback/sms.php',
			'VoiceFallbackMethod' => 'GET',
			'SmsFallbackMethod' => 'GET',
			'SmsMethod' => 'POST',
			'ApiVersion' => '2010-04-01',
		);
		
		try {
			$account = OpenVBX::getAccount();
			if(!$is_local) 
			{
				// toll-free
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
				// local
				$search_params = array();
				if (!empty($area_code))
				{
					$param = in_array($country, self::$areaCodeCountries) ? 'AreaCode' : 'Contains';
					$search_params[$param] = $area_code;
				}
				$numbers = $account->available_phone_numbers->getList($country, 'Local', $search_params);

				if (count($numbers->available_phone_numbers))
				{
					$params['PhoneNumber'] = $numbers->available_phone_numbers[0]->phone_number;
				}
				else
				{
					if (!empty($area_code))
					{
						$message = 'Could not find any numbers with "'.$area_code.'". '.
								'Please try again later or try a different prefix.';
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
	 * @throws VBX_IncomingNumberException
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

	protected function clear_cache()
	{
		$ci =& get_instance();
		$ci->api_cache->invalidate(__CLASS__, $ci->tenant->id);
	}

	/**
	 * @param $params
	 * @return stdClass
	 * @throws VBX_IncomingNumberException
	 */
	public static function get($params)
	{
		if (empty($params['number_sid']) && empty($params['phone_number']))
		{
			return false;
		}
		
		$vbx_incoming_numbers = new self;
		$numbers = $vbx_incoming_numbers->get_numbers();
		$incoming_number = false;
		
		if (!empty($numbers))
		{
			foreach ($numbers as $number)
			{
				switch (true)
				{
					case !empty($params['number_sid']):
						if ($number->id == $params['number_sid'])
						{
							$incoming_number = $number;
						}
						break;
					case !empty($params['phone_number']):
						if ($number->phone_number == $params['phone_number'])
						{
							$incoming_number = $number;
						}
						break;
				}				
			}
		}
		
		return $incoming_number;
	}
	
	/**
	 * Modified base_url to substitute 'localhost' for '127.0.0.1' so that
	 * first time local phone number setup works against Twilio's callback
	 * url validation.
	 */
	protected function base_url() {
		$base_url = base_url();
		
		if (strpos($base_url, '://localhost')) {
			str_replace('://localhost', '://127.0.0.1', $base_url);
		}
				
		return $base_url;
	}
}
