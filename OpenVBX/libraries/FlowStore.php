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
	
class FlowStoreException extends Exception {}

class FlowStore
{
	public static $flow_id;

	public static function setFlowId($flow_id)
	{
		self::$flow_id = $flow_id;
	}
	
	public static function get($key, $default)
	{
		try
		{
			$store = VBX_Flow_Store::get(array('key' => $key,
											   'flow_id' => self::$flow_id));
			if(!$store)
				return $default;
			
			return json_decode($store->value);
		}
		catch(VBX_Flow_StoreException $e)
		{
			error_log($e->getMessage());
			throw new FlowStoreException("Failed to access flow store: ". $e->getMessage());
		}
	}

	public static function set($key, $value)
	{
		if(is_null(self::$flow_id))
		{
			throw new FlowStoreException("Flow id not set");
		}
		
		try
		{
			$store = VBX_Flow_Store::get(array('key' => $key,
											   'flow_id' => self::$flow_id));
			
			if(!$store)
			{
				$store = new VBX_Flow_Store();
				$store->key = $key;
				$store->flow_id = self::$flow_id;
				$store->value = json_encode($value);
				$store->save();
			}
			else
			{
				$store->value = json_encode($value);
				$store->save(($force_update = true));
			}
		}
		catch(VBX_Flow_StoreException $e)
		{
			error_log($e->getMessage());
			error_log("VBX_Flow_StoreException while setting values for $key => ". var_export($value, true));
			throw new FlowStoreException("Failed to set values in flow store: ". $e->getMessage());
		}
	}

	public static function delete($key)
	{
		try
		{
			$store = VBX_Flow_Store::get(array('key' => $key,
											   'flow_id' => self::$flow_id));
			$store->delete();
		}
		catch(VBX_Flow_StoreException $e)
		{
			error_log($e->getMessage());
			error_log("VBX_Flow_StoreException while deleting `$key`");
			throw new FlowStoreException("Failed to set values in flow store: ". $e->getMessage());
		}
	}
}