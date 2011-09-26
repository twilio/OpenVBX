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
	
class PhoneNumberException extends Exception {}
class PhoneNumber
{

	const TYPE_DOMESTIC = 1;
	const TYPE_INTERNATIONAL = 2;
	const TYPE_PREMIUM = 3;
	const TYPE_UNKNOWN = 4;
	const TYPE_TOLLFREE = 5;
	const TYPE_DOMESTIC_INVALID = 6;
	const TYPE_SIP = 7;
		
	public function validatePhoneNumber($number) {

		// get type
		$normalized = self::normalizePhoneNumberToE164($number); // analyze will alter the number to a normalized form
		switch($type = self::analyzePhoneNumber($normalized)) {
				
			case self::TYPE_DOMESTIC:
			case self::TYPE_TOLLFREE:
			case self::TYPE_INTERNATIONAL:
			case self::TYPE_SIP:
				return $type;
			default:
				throw new PhoneNumberException("Number is not a US, Canadian or toll free phone number");
				break;
				
		}
	}
		
		
	public static function convertAlphaNumeric($phone) {

		// conver letters to numbers
		return str_ireplace(array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'), array('2','2','2','3','3','3','4','4','4','5','5','5','6','6','6','7','7','7','7','8','8','8','9','9','9','9'), $phone);
			
	}
		
	public static function normalizePhoneNumberToE164($phone) {
		if (strpos($phone, 'sip:') !== false)
			return $phone;

		// convert letters to numbers
		$phone = self::convertAlphaNumeric($phone);

		// get rid of any non (digit, + character)
		$phone = preg_replace('/[^0-9+]/', '', $phone);
			
		// validate intl 10
		if(preg_match('/^\+([2-9][0-9]{9})$/', $phone, $matches)){
			return "+{$matches[1]}";
		}
			
		// validate US DID
		if(preg_match('/^\+?1?([2-9][0-9]{9})$/', $phone, $matches)){
			return "+1{$matches[1]}";
		}
				

		// validate INTL DID
		if(preg_match('/^\+?([2-9][0-9]{8,14})$/', $phone, $matches)){
			return "+{$matches[1]}";
		}
				
		// premium US DID
		if(preg_match('/^\+?1?([2-9]11)$/', $phone, $matches)){
			return "+1{$matches[1]}";
		}
				
		return false;
			
	}
		
	public static function analyzePhoneNumber($phone) {
		if (strpos($phone, 'sip:') !== false) {
			return self::TYPE_SIP;
		}

		// normalize for letters
		$phone = self::normalizePhoneNumberToE164($phone);
			
		if(self::isShortPremium($phone)) {
			return self::TYPE_PREMIUM;
		}
			
		// if it's a north american number
		if(self::isNorthAmericanDID($phone))  {
				
			// check that it's not premium
			if(self::isNorthAmericanPremiumDID($phone))
				return self::TYPE_PREMIUM;
					
			// check if it's toll free
			if(preg_match('/^\+?1?(8([0-9])\2)|(88[0-9])[0-9]{7}$/', $phone))
				return self::TYPE_TOLLFREE;
					
			return self::TYPE_DOMESTIC;
		} 
			
		// wasn't north american, check for international
		if($tmp = self::isInternationalDID($phone)) {
			return self::TYPE_INTERNATIONAL;
		}
			
		// unkonwn type
		return self::TYPE_UNKNOWN;

			
	}
		
	public static function isNorthAmericanDID($phone) {

		// get rid of any non (digit, + character)
		$phone = preg_replace('/[^0-9+]/', '', $phone);

		// validate North American DID
		if(preg_match('/^\+1([2-9][0-9]{9})$/', $phone, $matches))
			return true;
		return false;
	}
		
	public static function isShortPremium($phone) {
		// emergency and info services
		if(preg_match('/^\+?1?([0-9]11)$/', $phone, $matches)) 
			return true;
		return false;
			
	}
		
	public static function isNorthAmericanPremiumDID($phone) {

		// get rid of any non (digit, + character)
		$phone = preg_replace('/[^0-9+]/', '', $phone);

		// validate North American Premium DID (toll services)
		if(preg_match('/^\+?1?(((900)|(976))[0-9]{7})$/', $phone, $matches)) 
			return true;
		// full dial information services
		if(preg_match('/^\+?1?([2-9][0-9]{2}5551212)$/', $phone, $matches))
			return true;
		return false;
			
	}

	public static function isInternationalDID($phone) {

		// get rid of any non (digit, + character)
		$phone = preg_replace('/[^0-9+]/', '', $phone);

		// validate INTL DID
		if(preg_match('/^\+?([2-9][0-9]{8,14})$/', $phone, $matches))
			return true;
		return false;
			
	}
		
	public static function decodeType($type) {

		switch($type) {
			case self::TYPE_DOMESTIC:
				return "Domestic";
				break;
			case self::TYPE_INTERNATIONAL:
				return "International";
				break;
			case self::TYPE_PREMIUM:
				return "Domestic Premium";
				break;
			case self::TYPE_TOLLFREE:
				return "Toll Free";
				break;
			case self::TYPE_DOMESTIC_INVALID:
				return "Domestic Invalid";
				break;
			default:
				return "Unkown";
				break;
		}
			
	}
		
	public static function normalizeE164ForDisplay($e164Number) {
		preg_match("/^\+?1?([2-9][0-9]{8,13})$/",$e164Number,$match);
		if(strlen($match[1]))
			return $match[1];
		else return $e164Number;
	}
		
}