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
	
class AppletInstance
{
	private static $id = '';
	private static $instance = array();
	private static $flow = null;
	private static $baseURI = null;
	public static $plugin = null;
	private static $pluginName = '';
	private static $appletName = '';
	private static $flowType = '';
	
	public static function getPluginStoreKey($name)
	{
		return self::$id . '::'. $name;
	}

	public static function getPlugin()
	{
		return self::$plugin;
	}

	public static function setFlowType($flow_type)
	{
		self::$flowType = $flow_type;
	}
	
	public static function getFlowType()
	{
		return self::$flowType;
	}
	
	public static function getValue($name, $default = '')
	{
		/* If this is an array selection: name[] or name[1]
		 * return an array or value of the array,
		 * otherwise handle as specific value
		 */

		/* If AppletInstance singleton is empty */
		if(empty(self::$instance))
		{
			return $default;
		}
		
		/* Match arrays or value of array */
		$regex_match_array = '/(.*)\[(.*)\]$/';
		if(preg_match($regex_match_array, $name, $matches))
		{

			/* Grab all the properties from the instance and
			 * find the ones that match the selector
			 */
			$keys = get_object_vars(self::$instance);			
			$container = $matches[1];
			$container_key = $matches[2];

			/* Initalize return value based on selector key */
			$list = '';
			if(empty($container_key))
			{
				$list = array();
			}
			
			foreach($keys as $key => $value)
			{
				if(preg_match('/^'.$container.'\[\]$/', $key, $inner_matches))
				{
					if(is_array($value))
					{
						/* Value of array selection: items[3] */
						if(is_string($container_key) && strlen($container_key) > 0)
						{
							$list = array_key_exists($container_key, $value)? $value[$container_key] : '';
						}
						/* Array selection: items[] */
						else
						{
							$list = $value;
						}
					}
					/* Everything else */
					else
					{
						$list = $value;
					}
				}
			}
			
			if(is_null($list))
			{
                return $default;
			}

            return $list;
		}

		return isset(self::$instance->$name)? self::$instance->$name : $default;
	}
	public static function getDropZoneValue($name = 'dropZone')
	{
		$value = self::getValue($name);

		return $value;
	}

	public static function getDropZoneUrl($name = 'dropZone')
	{
		$values = self::getDropZoneValue($name);
		if(empty($values))
		{
			return '';
		}

		if(is_string($values))
		{
			$values = array($values);
		}

		/* Build drop zone urls from values */
		$urls = array();
		foreach($values as $i => $value)
		{
			if(empty($value))
			{
				$urls[$i] = '';
				continue;
			}
			$parts = explode('/', $value);
			$value = $parts[count($parts) - 1];
			
			$urls[$i] = join('/', array(
										self::$baseURI,
										$value));
		}

		if(count($urls) > 1)
		{
			return $urls;
		}
		
		return !empty($urls)? $urls[0] : '';
	}
	
	public static function getSmsBoxValue($name = 'smsBox', $default = '')
	{
		$value = self::getValue($name, $default);

		return $value;
	}
	
	public static function getTextBoxValue($name = 'textBox', $default = '')
	{
		$value = self::getValue($name, $default);

		return $value;
	}
	
	public static function getSpeechBoxValue($name = 'speechBox', $default = '')
	{
		$value = self::getValue($name, $default);

		return $value;
	}
	
	public static function getAudioSpeechPickerValue($name = 'audioSpeechPicker')
	{
		$mode = self::getValue($name.'_mode');
		$say = self::getValue($name.'_say');
		$play = self::getValue($name.'_play');
	
		if ($mode === 'play')
		{
			$matches = array();
			if (preg_match('/^vbx-audio-upload:\/\/(.*)/i', $play, $matches))
			{
				// This is a locally hosted file, and we need to return the correct
				// absolute URL for the file.
				return asset_url("audio-uploads/" . $matches[1]);
			}
			else
			{
				// We'll assume it's an absolute URL
				return $play;
			}
		}
		else if ($mode === 'say')
		{
			return $say;
		}
		else
		{
			return '';
		}
	}
	
	public static function getAudioPickerValue($name = 'audioPicker')
	{
		$value = self::getValue($name);

		return $value;
	}
	
	public static function getUserGroupPickerValue($name = 'userGroupPicker')
	{
		$ci = &get_instance();
		$ci->load->model('vbx_user');
		$ci->load->model('vbx_group');
		$owner_id = self::getValue($name . '_id');
		$owner_type = self::getValue($name . '_type');
		$owner = null;
		
		switch($owner_type)
		{
			case 'group':
				$owner = VBX_Group::get(array( 'id' => $owner_id ));
				break;
			case 'user':
				$owner = VBX_User::get($owner_id);
				break;
		}

		return $owner;
	}

	public static function setInstance($instance)
	{
		list($plugin_name, $applet_name) = explode('---', $instance->type);
		
 		self::$instance = $instance->data;
 		self::$id = $instance->id;
		self::$pluginName = $plugin_name;
		self::$appletName = $applet_name;
		self::$plugin = new Plugin(self::$pluginName);
		PluginData::setPluginId(self::$plugin->getPluginId());
	}

	public static function setFlow($flow)
	{
		self::$flow = $flow;
	}

	public static function getFlow()
	{
		return self::$flow;
	}

	public static function setBaseURI($baseURI)
	{
		self::$baseURI = $baseURI;
	}

	public static function getBaseURI()
	{
		return self::$baseURI;
	}

	public static function getInstanceId()
	{
		return self::$id;
	}

	public static function assocKeyValueCombine($keys, $values, $case_insensitive = true)
	{
		$result = array();
		/* Filter values and keys to build assoc item pairs */
		foreach($keys as $key_id => $key)
		{
			/* If using the same key over again - it will clobber so warn the user in the logs */
			$value = isset($values[$key_id])? $values[$key_id] : '';
			if($case_insensitive)
			{
				$key = strtolower($key);
			}
			
			if(isset($result[$key]))
			{
				error_log("Clobbering keys in assocKeyValueCombine, Key: $key, Old: {$result[$key]}, New: $value");
			}
			$result[$key] = $value;
		}
		
		return $result;
	}
}
