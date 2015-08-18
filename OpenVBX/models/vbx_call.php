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

class VBX_CallException extends Exception {}

/*
 * Call Class
 */
class VBX_Call extends Model {

	public $total = 0;
	const CACHE_TIME_SEC = 180;

	public function __construct()
	{
		parent::Model();
	}

	/**
	 * Get a list of calls
	 *
	 * @throws VBX_CallException
	 * @param int $offset
	 * @param int $page_size
	 * @return array
	 */
	public function get_calls($offset = 0, $page_size = 20)
	{
		$output = array();

		$page_cache = 'calls-'.$offset.'-'.$page_size;
		$total_cache = 'calls-total';
		
		$ci =& get_instance();
		$tenant = $ci->tenant->id;
		if ($cache = $ci->api_cache->get($page_cache, __CLASS__, $tenant)
			&& $cache_total = $ci->api_cache->get($total_cache, __CLASS__, $tenant))
		{
			$this->total = $cache_total;
			return $cache;
		}

		$page = floor(($offset + 1) / $page_size);
		try {
			$account = OpenVBX::getAccount();
			$calls = $account->calls->getIterator($page, $page_size, array());
			if (count($calls)) {
				$this->total = count($calls);
				foreach ($calls as $call) {
					$output[] = (object) Array(
						'id' => $call->sid,
						'caller' => format_phone($call->from),
						'called' => format_phone($call->to),
						'status' => $call->status,
						'start' => $call->start_time,
						'end' => $call->end_time,
						'seconds' => intval($call->recording_duration)
					);
				}
			}
		}
		catch (Exception $e) {
			throw new VBX_CallException($e->getMessage());
		}

		$ci->api_cache->set($page_cache, $output, __CLASS__, $tenant, self::CACHE_TIME_SEC);
		$ci->api_cache->set($total_cache, $this->total, __CLASS__, $tenant, self::CACHE_TIME_SEC);

		return $output;
	}

	/**
	 * Start an outbound call
	 *
	 * @throws VBX_CallException
	 * @param string $from - the user making the call, this is the device that'll be called first
	 * @param string $to - the call destination
	 * @param string $callerId - the number to use as the caller id
	 * @param string $rest_access - token to authenticate the twiml request
	 * @return void
	 */
	public function make_call($from, $to, $callerId, $rest_access)
	{
		try
		{
			PhoneNumber::validatePhoneNumber($from);
			// handle being passed an email address for calls to browser clients
			if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
				PhoneNumber::validatePhoneNumber($to);
			}
		}
		catch(PhoneNumberException $e)
		{
			throw new VBX_CallException($e->getMessage());
		}
		
		// don't normalize email addresses that are used to identify browser clients
		if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
			$to = PhoneNumber::normalizePhoneNumberToE164($to);
		}
		$callerId = PhoneNumber::normalizePhoneNumberToE164($callerId);
		$from = PhoneNumber::normalizePhoneNumberToE164($from);
		$twiml_url = site_url("twiml/dial").'?'.http_build_query(compact('callerId', 'to', 'rest_access'));

		try {
			$account = OpenVBX::getAccount();
			$account->calls->create($callerId, $from, $twiml_url);
		}
		catch (Exception $e) {
			throw new VBX_CallException($e->getMessage());
		}
	}


	public function make_call_path($to, $callerid, $path, $rest_access)
	{
		$recording_url = site_url("twiml/redirect/$path/$rest_access");
		try {
			$account = OpenVBX::getAccount();
			$account->calls->create($callerid,
										$to,
										$recording_url
									);
		}
		catch (Exception $e) {
			throw new VBX_CallException($e->getMessage());
		}		
	}

}
