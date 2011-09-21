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

	function get_sandbox()
	{
		if(function_exists('apc_fetch')) 
		{
			$success = FALSE;
			$sandbox = apc_fetch($this->cache_key.'sandbox', $success);

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
					$success = apc_store($this->cache_key.'sandbox', serialize($sandbox), self::CACHE_TIME_SEC);
				}
			}
		}
		catch (Exception $e) {
			throw new VBX_IncomingNumberException($e->getMessage);
		}

		return $sandbox;
	}

	function get_numbers($retrieve_sandbox = true)
	{
		if(function_exists('apc_fetch')) 
		{
			$success = FALSE;
			$data = apc_fetch($this->cache_key.'numbers'.$retrieve_sandbox, $success);
			if($data AND $success) 
			{
				$numbers = @unserialize($data);
				if(is_array($numbers)) return $numbers;
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
		$num->id = $item->sid ? $item->sid : 'Sandbox';
		$num->name = $item->friendly_name;
		$num->phone = format_phone($item->phone_number);
		$num->pin = $item->pin ? $item->pin : null;
		$num->sandbox = $item->pin ? true : false;
		$num->url = $item->voice_url;
		$num->method = $item->voice_method;
		$num->smsUrl = $item->sms_url;
		$num->smsMethod = $item->sms_method;

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
	function assign_flow($phone_id, $flow_id)
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

		try {
			$account = OpenVBX::getAccount();
			// purchase tollfree, uses AvailablePhoneNumbers to search first.
			if(!$is_local) 
			{
				$country = 'US';
				$numbers = $account->available_phone_numbers
													->getTollFree($country)
													->getList();
				
				if (count($numbers->available_phone_numbers)) 
				{
					$params['PhoneNumber'] = current($numbers->available_phone_numbers)->phone_number;
				}
				else 
				{
					throw new VBX_IncomingNumberException('Currently out of TollFree numbers. Please try again later.');
				}
			}
			else 
			{ 
				// purchase local
				if(!empty($area_code))
				{
					$params['AreaCode'] = $area_code;
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
	function delete_number($phone_id)
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
