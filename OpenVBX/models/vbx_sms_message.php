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
class VBX_Sms_messageException extends Exception {}

/*
 * SMS Message Class
 */
class VBX_Sms_message extends Model {

	public $total = 0;

	private static $message_statuses = array('sent', 'failed', 'sending', 'queued');

	const CACHE_TIME_SEC = 180;

	function __construct()
	{
		parent::Model();
		$ci = &get_instance();		
		$this->cache_key = $ci->twilio_sid . '_sms';
	}

	/**
	 * Get SMS Messages
	 *
	 * @throws VBX_Sms_messageException
	 * @param int $offset
	 * @param int $page_size
	 * @return array
	 */
	function get_messages($offset = 0, $page_size = 20)
	{
		$output = array();

		$ci =& get_instance();
		
		$tenant_id = $ci->tenant->id;
		$page_cache = 'messages-'.$offset.'-'.$page_size;
		$total_cache = 'messages-total';

		if ($cache = $ci->api_cache->get($page_cache, __CLASS__, $tenant_id) &&
			$cache_total = $ci->api_cache->get($total_cache, __CLASS__, $tenant_id))
		{
			$this->total = $cache_total;
			return $cache;
		}
		
		$page = floor(($offset + 1) / $page_size);
		
		try {
			$account = OpenVBX::getAccount();
			$messages = $account->messages->getIterator($page, $page_size, array());
			if (count($messages)) {
				$this->total = count($messages);
				foreach ($messages as $message) {
					$output[] = (object) Array(
						'id' => $message->sid,
						'from' => format_phone($message->from),
						'to' => format_phone($message->to),
						'status' => $message->status
					);
				}
			}
		}
		catch (Exception $e) {
			throw new VBX_Sms_messageException($e->getMessage());
		}

		$ci->api_cache->set($page_cache, $output, __CLASS__, $tenant_id, self::CACHE_TIME_SEC);
		$ci->api_cache->set($total_cache, $this->total, __CLASS__, $tenant_id, self::CACHE_TIME_SEC);

		return $output;
	}

	function send_message($from, $to, $message, $mediaUrls = null)
	{
		$from = PhoneNumber::normalizePhoneNumberToE164($from);
		$to = PhoneNumber::normalizePhoneNumberToE164($to);
		$mediaUrls = ($mediaUrls==''?null:$mediaUrls);
		
		try {
			$account = OpenVBX::getAccount();
			$response = $account->messages->sendMessage($from, $to, $message, $mediaUrls);
		}
		catch (Exception $e) {
			throw new VBX_Sms_messageException($e->getMessage());
		}

		if (!in_array($response->status, array('sent', 'queued'))) {
			throw new VBX_Sms_messageException('SMS delivery failed. An unknown error occurred'.
												' during delivery.');
		}
	}

}
