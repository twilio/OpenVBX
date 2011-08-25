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

	private $cache_key;

	public $total = 0;

	const CACHE_TIME_SEC = 180;

	function __construct()
	{
		parent::Model();
		$ci = &get_instance();
		$this->cache_key = $ci->twilio_sid . '_calls';
	}

	/**
	 * Get a list of calls
	 *
	 * @param string $offset 
	 * @param string $page_size 
	 * @return void
	 */
	function get_calls($offset = 0, $page_size = 20)
	{
		$output = array();

		$page_cache_key = $this->cache_key . "_{$offset}_{$page_size}";
		$total_cache_key = $this->cache_key . '_total';

		if(function_exists('apc_fetch')) {
			$success = FALSE;

			$total = apc_fetch($total_cache_key, $success);
			if($total AND $success) $this->total = $total;

			$data = apc_fetch($page_cache_key, $success);

			if($data AND $success) {
				$output = @unserialize($data);
				if(is_array($output)) return $output;
			}
		}

		$page = floor(($offset + 1) / $page_size);
		try {
			$service = OpenVBX::getService();
			$calls = $service->account->calls->getIterator($page, $page_size, array());
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

		if(function_exists('apc_store')) {
			apc_store($page_cache_key, serialize($output), self::CACHE_TIME_SEC);
			apc_store($total_cache_key, $this->total, self::CACHE_TIME_SEC);
		}

		return $output;
	}

	/**
	 * Start an outbound call
	 *
	 * @param string $from - the user making the call, this is the device that'll be called first
	 * @param string $to - the call destination
	 * @param string $callerid - the number to use as the caller id
	 * @param string $rest_access - token to authenticate the twiml request
	 * @return void
	 */
	function make_call($from, $to, $callerid, $rest_access)
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
		$callerid = PhoneNumber::normalizePhoneNumberToE164($callerid);
		$from = PhoneNumber::normalizePhoneNumberToE164($from);
		$recording_url = site_url("twiml/dial").'?'.http_build_query(compact('callerid', 'to', 'rest_access'));

		try {
			$service = OpenVBX::getService();
			$service->account->calls->create($callerid,
											$from,
											$recording_url
										);
		}
		catch (Exception $e) {
			throw new VBX_CallException($e->getMessage());
		}
	}


	function make_call_path($to, $callerid, $path, $rest_access)
	{
		$recording_url = site_url("twiml/redirect/$path/$rest_access");
		try {
			$service = OpenVBX::getService();
			$service->account->calls->create($callerid,
											$to,
											$recording_url
										);
		}
		catch (Exception $e) {
			throw new VBX_CallException($e->getMessage());
		}		
	}

}
