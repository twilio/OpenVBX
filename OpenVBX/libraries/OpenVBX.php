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

class OpenVBXException extends Exception {}
class OpenVBX {
	public static $currentPlugin = null;

	public static function query($sql)
	{
		return PluginData::sqlQuery($sql);
	}
	public static function one($sql)
	{
		return PluginData::one($sql);
	}

	public static function isAdmin() {
		$ci = &get_instance();
		$is_admin = $ci->session->userdata('is_admin');

		return ($is_admin == 1);
	}

	public static function getTwilioAccountType()
	{
		try
		{
			$ci = &get_instance();
			$ci->load->model('vbx_accounts');
			return $ci->vbx_accounts->getAccountType();
		}
		catch(VBX_AccountsException $e)
		{
			error_log($e->getMessage());
			self::setNotificationMessage($e->getMessage());
			return 'Full';
		}
	}

	public static function getCurrentUser()
	{
		$ci = &get_instance();
		$user_id = $ci->session->userdata('user_id');
		return VBX_User::get($user_id);
	}

	public static function getTwilioApiVersion()
	{
		$ci = &get_instance();
		$url = $ci->settings->get('twilio_endpoint', VBX_PARENT_TENANT);
		if(preg_match('/.*\/([0-9]+-[0-9]+-[0-9]+)$/', $url, $matches))
		{
			return $matches[1];
		}

		return null;
	}

	public static function addCSS($file)
	{
		$ci = &get_instance();
		$plugin = OpenVBX::$currentPlugin;
		$info = $plugin->getInfo();
		$path = $info['plugin_path'] .'/'. $file;
		if(!is_file($path))
			error_log("Warning: CSS file does not exists: {$path}");
		$url = implode('/', array('plugins', $info['dir_name'], $file));
		$ci->template->add_css($url);
	}

	public static function addJS($file)
	{
		$ci = &get_instance();
		$plugin = OpenVBX::$currentPlugin;
		$info = $plugin->getInfo();
		$path = $info['plugin_path'] .'/'. $file;
		if(!is_file($path))
			error_log("Warning: JS script does not exists: {$path}");
		$url = implode('/', array('plugins', $info['dir_name'], $file));
		$ci->template->add_js($url);
	}

	public static function setNotificationMessage($message)
	{
		$ci = &get_instance();
		$ci->session->set_flashdata('error', $message);
	}

	public static function getUsers($options = array(), $limit = -1, $offset = 0)
	{
		return VBX_User::search($options, $limit, $offset);
	}

	public static function getGroups($options = array(), $limit = -1, $offset = 0)
	{
		return VBX_Group::search($options, $limit, $offset);
	}

	public static function getFlows($options = array(), $limit = -1, $offset = 0)
	{
		return VBX_Flow::search($options, $limit, $offset);
	}

	public static function addVoiceMessage($owner,
										   $sid,
										   $caller,
										   $called,
										   $recording_url,
										   $duration)
	{
		return self::addMessage($owner, $sid, $caller, $called, $recording_url,
								$duration, VBX_Message::TYPE_VOICE, null);
	}

	public static function addSmsMessage($owner,
										 $sid,
										 $to,
										 $from,
										 $body,
										 $api_type='twilio')
	{
		return self::addMessage($owner, $sid, $to, $from, '',
								0, VBX_Message::TYPE_SMS, $body, true, 
								$api_type);
	}

	public static function addMessage($owner,
									  $sid,
									  $caller,
									  $called,
									  $recording_url,
									  $duration,
									  $type = VBX_Message::TYPE_VOICE,
									  $text = null,
									  $notify = false,
									  $api_type = 'twilio')
	{
		try
		{
			$ci = &get_instance();
			$ci->load->model('vbx_message');
			if(!is_object($owner))
			{
				throw new VBX_MessageException('owner is invalid');
			}

			$owner_type = get_class($owner);
			$owner_type = str_replace('vbx_', '', strtolower($owner_type));
			$owner_id = $owner->id;


			$message = new VBX_Message();
			$message->owner_type = $owner_type;
			$message->owner_id = $owner_id;
			$message->call_sid = $sid;
			$message->caller = $caller;
			$message->called = $called;
			if(is_string($text))
			{
				$message->content_text = $text;
			}
			$message->content_url = $recording_url;
			$message->size = $duration;

			$message->type = $type;
			$message->status = VBX_Message::STATUS_NEW;

			/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
			$message->api_type = $api_type;
			/** End Disruptive Technologies code **/

			return $ci->vbx_message->save($message, $notify);
		}
		catch(VBX_MessageException $e)
		{
			error_log($e->getMessage());
			return false;
		}
	}

	/* Returns the version from the php software on the server */
	public static function version()
	{
		$ci = &get_instance();
		$ci->load->model('vbx_settings');
		return $ci->vbx_settings->get('version', VBX_PARENT_TENANT);
	}

	/* Returns the version of the database schema */
	public static function schemaVersion()
	{
		$ci = &get_instance();
		$ci->load->model('vbx_settings');
		return $ci->vbx_settings->get('schema-version', VBX_PARENT_TENANT);
	}

	/* Returns the latest version of the schema on the server,
	 * regardless if its been imported */
	public static function getLatestSchemaVersion()
	{
		$updates = scandir(VBX_ROOT.'/updates/');
		foreach($updates as $i => $update)
		{
			$updates[$i] = intval(preg_replace('/.(sql|php)$/', '', $update));
		}

		sort($updates);
		return $updates[count($updates)-1];
	}

	public static function setPageTitle($title, $overwrite = false) {
		$ci = &get_instance();
		return $ci->template->write('title', $title, $overwrite);
	}
}
