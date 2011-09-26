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
		$this->load->library('ErrorMessages');

		$numbers = array();
		if(function_exists('apc_fetch')) {
			$success = FALSE;
			$data = apc_fetch($this->cache_key.'numbers'.$retrieve_sandbox, $success);
			if($data AND $success) {
				$numbers = @unserialize($data);
				if(is_array($numbers)) return $numbers;
			}
		}

		$errors = array();

		try {

			/** Twilio **/
			$items = array();
			if ($this->twilio_sid && $this->twilio_token) {
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
			}

		} catch (VBX_IncomingNumberException $e) {
			$message = ErrorMessages::message('twilio_api', $e->getCode());
			if ($message) {
				$errors[] = $message;
			} else {
				$errors[] = $e->getMessage();
			}
		}

		try {

			/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
			/** Tropo **/
			if ($this->tropo_username && $this->tropo_password) {
				// Loop through each application, and get a listing of all phone numbers
				try {
					$provisioner = new ProvisioningAPI($this->tropo_username,
						$this->tropo_password);
					$applications = json_decode($provisioner->viewApplications());
					$appNumbers = json_decode($provisioner->viewAddresses());
				} catch (Exception $e) {
					throw new VBX_IncomingNumberException('Failed to connect to Tropo.', 999);
				}
				foreach ($applications as $application) {
					// Get the numbers for this application
					// $numbersResult = json_decode(
					// 	$provisioner->viewAddresses($application->id));
					$numbersResult = array();
					foreach ($appNumbers as $number) {
						$appID = substr($number->application, 
							strrpos($number->application, '/')+1);
						if ($application->id == $appID)
							$numbersResult[] = $number;
					}

					// Assign the phono app address to the number
		            $numbers = array();
		            $appAddress = null;
		            $voiceToken = null;
		            $messagingToken = null;
					foreach ($numbersResult as $number) {
						if ($number->type == 'number') {
							$number->voiceUrl = $application->voiceUrl;
							$number->smsUrl = $application->messagingUrl;
							$number->appId = $application->id;
							$number->partition = $application->partition;
							$items[] = $number;
		                    $numbers[] = end(array_keys($items));
						}
		                else if ($number->type == 'sip') {
		                    $appAddress = preg_replace('/(\d*)\@(.*)/',
		                        'app:$1',
		                        $number->address);
		                }
		                else if ($number->type == 'token') {
		                    switch ($number->channel) 
		                    {
		                        case 'voice':
		                            $voiceToken = $number->token;
		                            break;
		                        case 'messaging':
		                            $messagingToken = $number->token;
		                            break;
		                        
		                        default:
		                            throw new Exception('Invalid token.');
		                            break;
		                    }
		                }
					}
		            // Add app address to the numbers
		            foreach ($numbers as $numberI) {
		                if ($appAddress)
		                    $items[$numberI]->appAddress = $appAddress;
		                if ($voiceToken)
		                    $items[$numberI]->voiceToken = $voiceToken;
		                if ($messagingToken)
		                    $items[$numberI]->messagingToken = $messagingToken;
		            }
				}
			}

		} catch (VBX_IncomingNumberException $e) {
			$message = ErrorMessages::message('twilio_api', $e->getCode());
			if ($message) {
				$errors[] = $message;
			} else {
				$errors[] = $e->getMessage();
			}
		} catch (Exception $e) {
			$errorMessage = null;
			if (strpos($e->getMessage(), 'Operation timed out') !== false)
				$errorMessage = 'Could not connect to Tropo.';
			if ($errorMessage)
				$errors[] = $errorMessage;
		}
		/** End Disruptive Technologies code **/

		if ($errors && !empty($errors)) {
			if (count($errors) == 1) {
				$this->session->set_flashdata('error', $errors[0]);
			} else {
				$errorMessage = '<ul>';
				foreach ($errors as $error) {
					$errorMessage .= "<li>$error</li>";
				}
				$errorMessage .= '</ul>';

				$this->session->set_flashdata('error', $errorMessage);
			}
		}

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

	/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
	function get_number_info($phone_id, $phone) 
	{
		$info = array();

		if (strpos($phone_id, 'tropo-') !== false) {
			$info['phone'] = $phone;
			$info['phone_friendly'] = format_phone($phone);
			$info['provider'] = 'tropo';

			$app_id = str_replace('tropo-', '', $phone_id);

			try {
				$provisioner = new ProvisioningAPI($this->tropo_username,
					$this->tropo_password);
				
				// Get the application
				$application = json_decode(
					$provisioner->viewSpecificApplication($app_id));

				if ($application->partition == 'staging') {
					$info['type'] = 'development';
				} else {
					$info['type'] = 'production';
				}

				// Get numbers
				$numbers = json_decode(
					$provisioner->viewAddresses($app_id));

				$info['siblings'] = array();
				foreach ($numbers as $number) {
					$address = $number->number ? $number->number :
						$number->address;
					if ($number->type == 'number') {
						$info['siblings'][] = format_phone($address);
					} else if ($number->type <> 'token') {
						$info['siblings'][] = $number->type . ': ' .
							$address;
					}
				}
			} catch (TropoException $e) {
				throw new VBX_IncomingNumberException('Failed to connect to Tropo.', 999);
			}
		} else {
			$info['provider'] = 'twilio';

			// Check if phone_id is "sandbox", we don't actually have to
			// hit the Twilio api for this call. Code included for possible
			// future additions.
			$info['type'] = $phone_id == 'Sandbox' ? 'development' : 
				'production';

			// $uri = "Accounts/{$this->twilio_sid}/IncomingPhoneNumbers/$phone_id";
			// /* Get IncomingNumbers */
			// try
			// {
			// 	$response = $this->twilio->request($uri);
			// }
			// catch(TwilioException $e)
			// {
			// 	throw new VBX_IncomingNumberException('Failed to connect to Twilio.', 503);
			// }

			// if($response->IsError)
			// {
			// 	throw new VBX_IncomingNumberException($response->ErrorMessage, $response->HttpStatus);
			// }

			// if(isset($response->ResponseXml->IncomingPhoneNumber))
			// {
			// 	$phoneNumber = $response->ResponseXml->IncomingPhoneNumber;
			// 	// TODO: Add twilio specific code here
			// }
		}

		return $info;
	}
	/** End Disruptive Technologies code **/

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
		if (isset($item->href)) {
			/** Tropo **/
			$num->flow_id = null;
			$num->id = 'tropo-'.(string) ($item->appId);
			$num->name = (string) ($item->number);
			$num->phone = format_phone($item->number);
			$num->raw_phone = (string) $item->number;
			$num->pin = null;
			$num->sandbox = $item->partition == 'staging' ? true : false;
			$num->url = (string) $item->voiceUrl;
			$num->method = 'POST';
			$num->smsUrl = (string) $item->smsUrl;
			$num->smsMethod = 'POST';
			$num->api_type = 'tropo';
			$num->app_address = @$item->appAddress;
            $num->voice_token = @$item->voiceToken;
            $num->messaging_token = @$item->messagingToken;

			$call_base = site_url('tropo/start') . '/';
			$base_pos = strpos($num->url, $call_base);
			$num->installed = ($base_pos !== FALSE);
		} else {	
			/** Twilio **/
			$num->flow_id = null;
			$num->id = (string) isset($item->Sid)? (string) $item->Sid : 'Sandbox';
			$num->name = (string) $item->FriendlyName;
			$num->phone = format_phone($item->PhoneNumber);
			$num->raw_phone = (string) $item->PhoneNumber;
			$num->pin = isset($item->Pin)? (string)$item->Pin : null;
			$num->sandbox = isset($item->Pin)? true : false;
			$num->url = (string) $item->VoiceUrl;
			$num->method = (string) $item->VoiceMethod;
			$num->smsUrl = (string) $item->SmsUrl;
			$num->smsMethod = (string) $item->SmsMethod;
			$num->api_type = 'twilio';
			$num->app_address = null;
            $num->voice_token = null;
            $num->messaging_token = null;

			$call_base = site_url('twiml/start') . '/';
			$base_pos = strpos($num->url, $call_base);
			$num->installed = ($base_pos !== FALSE);
		}

		$matches = array();

		if (!preg_match('/\/(voice|sms)\/(\d+)$/', $num->url, $matches) == 0)
		{
			$num->flow_id = intval($matches[2]);
		}

		return $num;
	}

	function assign_flow($phone_id, $flow_id)
	{
		if (strpos($phone_id, 'tropo-') === 0) {
			/** Tropo Number **/
			$appId = substr($phone_id, strlen('tropo-'));

			$voice_url = site_url("tropo/start/voice/$flow_id");
			$sms_url = site_url("tropo/start/sms/$flow_id");

			try {
				$provisioner = new ProvisioningAPI($this->tropo_username, 
					$this->tropo_password);
				
				$response = $provisioner->updateApplicationProperty($appId,
					NULL,
					NULL,
					$voice_url,
					$sms_url);

			} catch (Exception $e) {
				throw new VBX_IncomingNumberException($e->getMessage());
			}
		} else {
			/** Twilio Number **/
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
		}

		return TRUE;
	}

	/**
	 * Upgrade a tropo application.
	 *
	 * @param string $phone_id Tropo application ID in format: tropo-APPID e.g. tropo-12345.
	 */
	function upgrade_tropo_app($phone_id)
	{
		$app_id = str_replace('tropo-', '', $phone_id);

		if (empty($app_id) || !is_numeric($app_id)) {
			throw new VBX_IncomingNumberException('Invalid phone number.');
		}

		try {
			$provisioner = new ProvisioningAPI($this->tropo_username,
				$this->tropo_password);
			
			// Update app to production
			$provisioner->updateApplicationProperty($app_id, 
				null, 
				null,
				null,
				null,
				null,
				'production');
		} catch (Exception $e) {
			throw new VBX_IncomingNumberException('Unable to upgrade application to production. Please check your account at tropo.com.');
		}
	}

	// purchase a new phone number, return the new number
	function add_number($is_local, $area_code, $api_type, $number_sibling='',
		$country_code=1)
	{
		if($is_local
		   && (
			   !empty($area_code) &&
			   (strlen(trim($area_code)) != 3 ||
				preg_match('/([^0-9])/', $area_code) > 0)))
		{
			throw new VBX_IncomingNumberException('Area code invalid');
		}

		if ($api_type == 'twilio') {

			$voice_url = site_url("twiml/start/voice/0");
			$sms_url = site_url("twiml/start/sms/0");

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

		} else {

			if (!$is_local) {
				$area_code = "866"; // Toll free area code
			}
			
			/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
			try {
				if ($country_code == '1')
					$prefix = $country_code.$area_code;
				else
					$prefix = $country_code;
				
				$provisioner = new ProvisioningAPI($this->tropo_username, 
					$this->tropo_password);

				if (!$number_sibling) {
					$voice_url = site_url("tropo/start/voice/0");
					$sms_url = site_url("tropo/start/sms/0");

					// Create a tropo application
					$response = json_decode($provisioner->createApplication(
						'', 
						'OpenVBX',
						$voice_url,
						$sms_url,
						'webapi',
						'staging'
						));

					$app_id = substr($response->href, 
						strrpos($response->href, '/')+1);
					
					// Add outbound tokens
					for ($i=0; $i < 2; $i++) { 
						$channel = $i == 0 ? 'voice' : 'messaging';
						$postData = json_encode(
							array('type'=>'token', 'channel'=>$channel));
						$ch = curl_init("http://api.tropo.com/v1/applications/$app_id/addresses");
						curl_setopt($ch, CURLOPT_USERPWD, 
							"{$this->tropo_username}:{$this->tropo_password}");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_HTTPHEADER, 
							array(
								'Content-Type: application/json', 
								'Content-Length: '.strlen($postData)
								));
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
						$result = curl_exec($ch);
						curl_close($ch);
					}
				} else {
					$app_id = str_replace('tropo-', '', $number_sibling);
				}

				// Add the phone number
				$response = json_decode($provisioner->updateApplicationAddress(
					$app_id,
					'number',
					// $area_code
					$prefix
				));

				if ($response->href) {
					$ch = curl_init($response->href);
					curl_setopt($ch, CURLOPT_USERPWD, 
						"{$this->tropo_username}:{$this->tropo_password}");
					curl_setopt($ch, CURLOPT_HTTPGET, true);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					$response = json_decode(curl_exec($ch));

					return $this->parseIncomingPhoneNumber($response);
				}
			} catch (Exception $e) {
				// Delete application if blank
				$provisioner = new ProvisioningAPI($this->tropo_username,
					$this->tropo_password);
				$deleteApp = true;
				$addresses = json_decode($provisioner->viewAddresses($app_id));
				foreach ($addresses as $address) {
					if ($address->type == 'number') {
						$deleteApp = false;
						break;
					}
				}
				if ($deleteApp) {
					$response = $provisioner->deleteApplication($app_id);
				}

				if (strpos($e->getMessage(), 'Invalid HTTP response returned: 503') !== false) {
					throw new VBX_IncomingNumberException('Invalid area code.');
				} else if (strpos($e->getMessage(), 'Operation timed out after') !== false) {
					throw new VBX_IncomingNumberException('Tropo timed out during the request. Perhaps the area code is invalid?');
				} else {
					throw new VBX_IncomingNumberException($e->getMessage());
				}
			}
			/** End Disruptive Technologies code **/

		}
	}

	// purchase a new phone number, return the new number
	function delete_number($phone_id, $phone_number = '')
	{
		if (strpos($phone_id, 'tropo-') === false) {
			$rest_url = "Accounts/{$this->twilio_sid}/IncomingPhoneNumbers/$phone_id";

			$response = $this->twilio->request($rest_url, 'DELETE');

			if($response->IsError) throw new VBX_IncomingNumberException($response->ErrorMessage);
		} else {
			$app_id = str_replace('tropo-', '', $phone_id);

			try {
				$provisioner = new ProvisioningAPI($this->tropo_username, $this->tropo_password);

				$response = $provisioner->deleteApplicationAddress($app_id, 'number', $phone_number);

				// Delete application if blank
				$provisioner = new ProvisioningAPI($this->tropo_username,
					$this->tropo_password);
				$deleteApp = true;
				$addresses = json_decode($provisioner->viewAddresses($app_id));
				foreach ($addresses as $address) {
					if ($address->type == 'number') {
						$deleteApp = false;
						break;
					}
				}

				if ($deleteApp) {
					$response = $provisioner->deleteApplication($app_id);
				}
			} catch (Exception $e) {
				throw new VBX_IncomingNumberException($e->getMessage());
			}
		}

		$this->clear_cache();

		return TRUE;
	}

}
