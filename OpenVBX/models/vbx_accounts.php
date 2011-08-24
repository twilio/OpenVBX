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

class VBX_AccountsException extends Exception {}

class VBX_Accounts extends Model
{
	private $cache_key;

	const CACHE_TIME_SEC = 60;

	public function __construct()
	{
		parent::__construct();
		
		$ci = &get_instance();
		$this->cache_key = $ci->twilio_sid . '_accounts' . date('z-H');
	}

	function getAccountType()
	{
		if(function_exists('apc_fetch')) {
			$success = FALSE;
			$type = apc_fetch($this->cache_key, $success);
			
			if($type AND $success) {
				return $type;
			}
		}

		try {
			$service = OpenVBX::getService();
			$account_type = $service->account->type;
		}
		catch (Exception $e) {
			throw new VBX_AccountsException($e->getMessage());
		}

		if(function_exists('apc_store')) {
			$success = apc_store($this->cache_key, $account_type, self::CACHE_TIME_SEC);
		}

		return $account_type;
	}

	private function clear_cache()
	{
		if(function_exists('apc_delete'))
		{
			apc_delete($this->cache_key);
			return TRUE;
		}
		
		return FALSE;
	}

}