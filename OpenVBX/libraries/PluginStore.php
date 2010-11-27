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

 class PluginStoreException extends Exception {}

/* WARNING: this class will be depcrated in 0.75 */
class PluginStore {

	public static function startswith($key, $default = null)
	{
		error_log('Deprecating in 0.75: '.__FUNCTION__);
		try
		{
			return PluginData::startswith($key, $default);
		}
		catch(PluginDataException $e)
		{
			throw new PluginStoreException($e->getMessage());
		}
	}
	
	public static function get($key, $default = null)
	{
		error_log('Deprecating in 0.75: '.__FUNCTION__);
		try
		{
			return PluginData::get($key, $default);
		}
		catch(PluginDataException $e)
		{
			throw new PluginStoreException($e->getMessage());
		}
	}

	public static function set($key, $value)
	{
		error_log('Deprecating in 0.75: '.__FUNCTION__);
		try
		{
			PluginData::set($key, $value);
		}
		catch(PluginDataException $e)
		{
			throw new PluginStoreException($e->getMessage());
		}
	}

	public static function delete($key)
	{
		error_log('Deprecating in 0.75: '.__FUNCTION__);
		try
		{
			PluginData::delete($key);
		}
		catch(PluginDataException $e)
		{
			throw new PluginStoreException($e->getMessage());
		}
	}

	public static function getKeyValues()
	{
		error_log('Deprecating in 0.75: '.__FUNCTION__);
		try
		{
			PluginData::getKeyValues();
		}
		catch(PluginDataException $e)
		{
			throw new PluginStoreException($e->getMessage());
		}
	}

}