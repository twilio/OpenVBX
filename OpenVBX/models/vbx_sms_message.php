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
require_once(APPPATH . 'libraries/tropo/tropo.class.php');
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
		error_log($_SERVER['REQUEST_URI']);
		error_log("TWILIO SID: ".$ci->twilio_sid);
		$this->twilio_sid = $ci->twilio_sid;
		$this->twilio = new TwilioRestClient($ci->twilio_sid,
											 $ci->twilio_token,
											 $ci->twilio_endpoint);

		$this->tropo_username = $ci->tropo_username;
		$this->tropo_password = $ci->tropo_password;
		
		$this->cache_key = $this->twilio_sid . '_sms';
	}

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
		$params = array('num' => $page_size, 'page' => $page);
		$response = $this->twilio->request("Accounts/{$this->twilio_sid}/SMS/Messages", 'GET', $params);

		if($response->IsError)
		{
			throw new VBX_Sms_messageException($response->ErrorMessage, $response->HttpStatus);
		}
		else
		{

			$this->total = (string) $response->ResponseXml->SMSMessages['total'];
			$records = $response->ResponseXml->SMSMessages->SMSMessage;

			foreach($records as $record)
			{
				$item = new stdClass();
				$item->id = (string) $record->Sid;
				$item->from = format_phone($record->From);
				$item->to = format_phone($record->To);
				$item->status = (string)$record->Status;

				$output[] = $item;
			}
		}

		if(function_exists('apc_store')) {
			apc_store($page_cache_key, serialize($output), self::CACHE_TIME_SEC);
			apc_store($total_cache_key, $this->total, self::CACHE_TIME_SEC);
		}

		return $output;
	}

	function send_message($from, $to, $message, $api_type='twilio')
	{
		$from = PhoneNumber::normalizePhoneNumberToE164($from);
		$to = PhoneNumber::normalizePhoneNumberToE164($to);

		if ($api_type == 'twilio') {

			$twilio = new TwilioRestClient($this->twilio_sid,
										   $this->twilio_token,
										   $this->twilio_endpoint);
			error_log("Sending sms from $from to $to with content: $message");
			$response = $twilio->request("Accounts/{$this->twilio_sid}/SMS/Messages",
										 'POST',
										 array( "From" => $from,
												"To" => $to,
												"Body" => $message,
												)
										 );
			$status = isset($response->ResponseXml)? $response->ResponseXml->SMSMessage->Status : 'failed';
			if($response->IsError ||
			   ($status != 'sent' && $status != 'queued'))
			{
				error_log("SMS not sent - Error Occurred");
				error_log($response->ErrorMessage);
				throw new VBX_Sms_messageException($response->ErrorMessage);
			}

		} else if ($api_type == 'tropo') {

			/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
			// Loop through each application, and get a listing of all phone numbers
			try {
				$provisioner = new ProvisioningAPI($this->tropo_username,
					$this->tropo_password);
				$applications = json_decode($provisioner->viewApplications());
			} catch (Exception $e) {
				throw new VBX_Sms_messageException("Failed to connect to Tropo.");
			}
			$tokenMatch = false;
			foreach ($applications as $application) {
				if ($tokenMatch && $messagingToken)
					break;
				$tokenMatch = false;
				// Get the numbers for this application
				$numbersResult = json_decode($provisioner->viewAddresses($application->id));
				$messagingToken = null;
				foreach ($numbersResult as $number) {
					if ($number->type == 'number' 
					&& $number->number == $from) {
						$tokenMatch = true;
						if ($messagingToken)
							break;
					} else if ($number->type == 'token' 
					&& $number->channel == 'messaging') {
						$messagingToken = $number->token;
						if ($tokenMatch) 
							break;
					}
				}
			}
			if ($tokenMatch && $messagingToken) {
				// Send SMS message by initiating the token 
				$ch = curl_init("http://api.tropo.com/1.0/sessions?action=create&token=$messagingToken&to=".urlencode($to)."&message=".urlencode($message)."&from=".urlencode($from));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$result = curl_exec($ch);
				curl_close($ch);
			} else {
				throw new VBX_Sms_messageException("Error sending SMS message - no token defined.");
			}
			/** End Disruptive Technologies code **/

		} else {
			throw new VBX_Sms_messageException('Cannot send SMS message - invalid API type.');
		}
	}

}
