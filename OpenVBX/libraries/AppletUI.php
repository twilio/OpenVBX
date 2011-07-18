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

require_once 'AppletUI/init.php';

class AppletUI {

	public static function timeRange($name, $from, $to)
	{
		$widget = new TimeRangeWidget($name, $from, $to);
		return $widget->render();
	}

	public static function userGroupPicker($name = 'userGroupPicker', $label = 'Select a User or Group')
	{
		$value = AppletInstance::getUserGroupPickerValue($name);
		$widget = new UserGroupPickerWidget($name, $label, $value);
		return $widget->render();
	}

	public static function audioPicker($name = 'audioPicker')
	{
		$value = AppletInstance::getAudioPickerValue($name);
		$widget = new AudioPickerWidget($name, $value);
		return $widget->render();
	}
	
	public static function audioSpeechPicker($name = 'audioSpeechPicker')
	{
		$value = AppletInstance::getAudioSpeechPickerValue($name);

		$mode = null;
		$say = null;
		$play = null;

		if (preg_match('/^http(s)?:\/\//i', $value) ||
			preg_match('/^vbx-audio-upload:\/\//i', $value))
		{
			$mode = 'play';
			$play = $value;
		}
		else if (!empty($value))
		{
			$mode = 'say';
			$say = $value;
		}
		
		$widget = new AudioSpeechPickerWidget($name, $mode, $say, $play);
		
		return $widget->render();
	}
	
	public static function speechBox($name = 'speechBox')
	{
		$value = AppletInstance::getSpeechBoxValue($name);
		$widget = new SpeechBoxWidget($name, $value);
		return $widget->render();
	}
	
	public static function textBox($name = 'textBox')
	{        
		$value = AppletInstance::getTextBoxValue($name);
		$widget = new TextBoxWidget($name, $value);
		return $widget->render();
	}
		
	public static function smsBox($name = 'smsBox')
	{
		$value = AppletInstance::getSmsBoxValue($name);
		$widget = new SmsBoxWidget($name, $value);
		return $widget->render();
	}

	public static function dropZone($name = 'dropZone', $label = 'Drop applet here')
	{
		$link = AppletInstance::getDropZoneValue($name);
		$applet_id = null;
		$type = '';
		$icon_url = '';
		
		if(!empty($link) && is_string($link))
		{
			$applet_id = explode('/', $link);
			$applet_id = $applet_id[count($applet_id)-1];
		}
		
		if(!empty($applet_id) &&
		   isset(Applet::$flow_data[$applet_id]))
		{
			$applet = Applet::$flow_data[$applet_id];
			$type = $applet->type;
			$icon_url = '';
			$label = $applet->name;
			
			$type_parts = explode("---", $type);
			$plugin_name = $type_parts[0];
			$applet_name = $type_parts[1];

			if(is_file('plugins/'.$plugin_name.'/applets/'.$applet_name.'/icon.png')) {
				$icon_url = asset_url('plugins/' . $plugin_name . '/applets/' . $applet_name . '/icon.png');
			} else {
				$icon_url = asset_url('assets/i/icon.png');
			}
		}
		else if(!isset(Applet::$flow_data[$applet_id])
			 && !empty($applet_id))
		{
			/* handling this gracefully in case of bad programmer */
			$applet_id = null;
			$link = null;
		}
		
		$widget = new DropZoneWidget($name, $label, $type, $icon_url, $link);
		return $widget->render();
	}

	public static function menu($name = 'menu' )
	{
		/* TODO */
	}
}
