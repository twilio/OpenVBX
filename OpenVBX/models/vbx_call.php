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
		$this->twilio = new TwilioRestClient($this->twilio_sid,
											 $this->twilio_token,
											 $this->twilio_endpoint);
		$this->cache_key = $this->twilio_sid . '_calls';
	}

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
		$params = array('num' => $page_size, 'page' => $page);
		$response = $this->twilio->request("Accounts/{$this->twilio_sid}/Calls", 'GET', $params);

		if($response->IsError)
		{
			throw new VBX_CallException($response->ErrorMessage, $response->HttpStatus);
		}
		else
		{

			$this->total = (string) $response->ResponseXml->Calls['total'];
			$records = $response->ResponseXml->Calls->Call;

			foreach($records as $record)
			{
				$item = new stdClass();
				$item->id = (string) $record->Sid;
				$item->caller = format_phone($record->From);
				$item->called = format_phone($record->To);
				$item->status = (string)$record->Status;
				$item->start = isset($record->StartTime) ? strtotime($record->StartTime) : null;
				$item->end = isset($record->EndTime) ? strtotime($record->EndTime) : null;
				$item->seconds = isset($record->RecordingDuration) ? (string) $record->RecordingDuration : 0;

				$output[] = $item;
			}
		}

		if(function_exists('apc_store')) {
			apc_store($page_cache_key, serialize($output), self::CACHE_TIME_SEC);
			apc_store($total_cache_key, $this->total, self::CACHE_TIME_SEC);
		}

		return $output;
	}

	function make_call($from, $to, $callerid, $rest_access)
	{
		try
		{
			PhoneNumber::validatePhoneNumber($from);
			PhoneNumber::validatePhoneNumber($to);
		}
		catch(PhoneNumberException $e)
		{
			throw new VBX_CallException($e->getMessage());
		}

		$callerid = PhoneNumber::normalizePhoneNumberToE164($callerid);
		$from = PhoneNumber::normalizePhoneNumberToE164($from);
		$to = PhoneNumber::normalizePhoneNumberToE164($to);
		
		$twilio = new TwilioRestClient($this->twilio_sid,
									   $this->twilio_token,
									   $this->twilio_endpoint);
		
		$recording_url = site_url("twiml/dial").'?'.http_build_query(compact('callerid', 'to', 'rest_access'));
		
		$response = $twilio->request("Accounts/{$this->twilio_sid}/Calls",
									 'POST',
									 array( "Caller" => $callerid,
											"Called" => $from,
											"Url" => $recording_url,
											)
									 );
		
		if($response->IsError) {
			error_log($from);
			throw new VBX_CallException($response->ErrorMessage);
		}
	}

	function make_call_tropo($from, $to, $callerid, $voice_token)
	{
        /** Updated, Disruptive Technologies, for Tropo VBX conversion **/
        // Loop through each application, and get a listing of all phone numbers
        try {
            $provisioner = new ProvisioningAPI($this->tropo_username,
                    $this->tropo_password);
            $applications = json_decode($provisioner->viewApplications());
        } catch (Exception $e) {
            throw new VBX_Sms_messageException("Failed to connect to Tropo.");
        }
        // Send SMS message by initiating the token 
        $ch = curl_init("http://api.tropo.com/1.0/sessions?action=create&token=$voice_token&to=".urlencode($to)."&callerid=".urlencode($callerid)."&from=".urlencode($from));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        /** End Disruptive Technologies code **/

		// try
		// {
		// 	PhoneNumber::validatePhoneNumber($from);
		// 	PhoneNumber::validatePhoneNumber($to);
		// }
		// catch(PhoneNumberException $e)
		// {
		// 	throw new VBX_CallException($e->getMessage());
		// }

		// $callerid = PhoneNumber::normalizePhoneNumberToE164($callerid);
		// $from = PhoneNumber::normalizePhoneNumberToE164($from);
		// $to = PhoneNumber::normalizePhoneNumberToE164($to);
		// 
		// $twilio = new TwilioRestClient($this->twilio_sid,
		// 							   $this->twilio_token,
		// 							   $this->twilio_endpoint);
		// 
		// $recording_url = site_url("tropo/dial").'?'.http_build_query(compact('callerid', 'to'));
		// 
		// $response = $twilio->request("Accounts/{$this->twilio_sid}/Calls",
		// 							 'POST',
		// 							 array( "Caller" => $callerid,
		// 									"Called" => $from,
		// 									"Url" => $recording_url,
		// 									)
		// 							 );
		// 
		// if($response->IsError) {
		// 	error_log($from);
		// 	throw new VBX_CallException($response->ErrorMessage);
		// }
	}


	function make_call_path($to, $callerid, $path, $rest_access)
	{
		$twilio = new TwilioRestClient($this->twilio_sid,
									   $this->twilio_token);
		
		$recording_url = site_url("twiml/redirect/$path/$rest_access");
		$response = $twilio->request("Accounts/{$this->twilio_sid}/Calls",
									 'POST',
									 array( "Caller" => PhoneNumber::normalizePhoneNumberToE164($callerid),
											"Called" => PhoneNumber::normalizePhoneNumberToE164($to),
											"Url" => $recording_url,
											)
									 );
		if($response->IsError) {
			error_log($from);
			error_log(var_export($response, true));
			throw new VBX_CallException($response->ErrorMessage);
		}
	}

}
