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

class VBX_OutgoingCallerIdException extends Exception {}

class VBX_Outgoing_caller_ids extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_caller_ids()
	{		
		$ci =& get_instance();
		$cache_key = 'outgoing-caller-ids';
		if ($cache = $ci->api_cache->get($cache_key, __CLASS__, $ci->tenant->id))
		{
			return $cache;
		}

		$caller_ids = array();
		try {
			$account = OpenVBX::getAccount();
			foreach ($account->outgoing_caller_ids as $caller_id) 
			{
				// check that caller_id is a proper instance type
				$caller_ids[] = $this->parseOutgoingCallerId($caller_id);
			}
		}
		catch (Exception $e) {
			$msg = 'Unable to fetch Numbers: ';
			switch ($e->getCode())
			{
				case 20003:
					$msg .= 'Authentication Failed.';
					break;
				default:
					$msg .= $e->getMessage();
			}
			throw new VBX_OutgoingCallerIdException($msg, $e->getCode());
		}

		$ci->api_cache->set('outgoing-caller-ids', $caller_ids, __CLASS__, $ci->tenant->id);

		return $caller_ids;
	}

	private function parseOutgoingCallerId($item)
	{
		$num = new stdClass();
		$num->id = $item->sid;
		$num->name = $item->friendly_name;
		$num->phone = format_phone($item->phone_number);
		$num->phone_number = $item->phone_number;
		$num->capabilities = new stdClass();
		$num->capabilities->voice = true;
		$num->capabilities->sms = false;

		return $num;
	}

	protected function clear_cache()
	{
		$ci =& get_instance();
		$ci->api_cache->invalidate(__CLASS__, $ci->tenant->id);
	}
}