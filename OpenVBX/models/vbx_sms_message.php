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

	private $cache_key;

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
	 * @param string $offset 
	 * @param string $page_size 
	 * @return void
	 */
	function get_messages($offset = 0, $page_size = 20)
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
			$messages = $service->account->sms_messages->getIterator($page, $page_size, array());
			if (count($messages)) {
				$this->total = count($messages); // @TODO need verification that this will work, return may not be Array compatible
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

		if(function_exists('apc_store')) {
			apc_store($page_cache_key, serialize($output), self::CACHE_TIME_SEC);
			apc_store($total_cache_key, $this->total, self::CACHE_TIME_SEC);
		}

		return $output;
	}

	function send_message($from, $to, $message)
	{
		$from = PhoneNumber::normalizePhoneNumberToE164($from);
		$to = PhoneNumber::normalizePhoneNumberToE164($to);
		
		try {
			$service = OpenVBX::getService();
			$response = $service->account->sms_messages->create($from,
																$to,
																$message
															);
		}
		catch (Exception $e) {
			throw new VBX_Sms_messageException($e->getMessage);
		}
		file_put_contents('/tmp/sms.php', print_r($response, true));
		if (!in_array($response->status, array('sent', 'queued'))) {
			throw new VBX_Sms_messageException('SMS delivery failed. An unknown error occurred during delivery.');
		}
	}

}
