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
	public function __construct()
	{
		parent::__construct();
	}

	function getAccountType()
	{
		$ci =& get_instance();
		if ($cache = $ci->api_cache->get('account-type', __CLASS__, $ci->tenant->id))
		{
			return $cache;
		}

		try {
			$account = OpenVBX::getAccount();
			$account_type = $account->type;
		}
		catch (Exception $e) {
			throw new VBX_AccountsException($e->getMessage());
		}

		$ci->api_cache->set('account-type', $account_type, __CLASS__, $ci->tenant->id);

		return $account_type;
	}
}