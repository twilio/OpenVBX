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
	
class ConfigException extends Exception {}

class Config extends User_Controller
{
	public function __construct()
	{
		parent::__construct();
		
	}

	public function index($plugin)
	{
		$this->admin_only('plugin config');
		$data = $this->init_view_data();

		$plugin = Plugin::get($plugin);
		PluginData::setPluginId($plugin->getPluginId());
		OpenVBX::$currentPlugin = $plugin;
		try
		{
			$data['info'] = $plugin->getInfo();
			$data['script'] = $plugin->getScript('config');
		}
		catch(PluginException $e)
		{
			error_log($e->getMessage());
			$data['script'] = null;
		}
		$this->respond('', 'page/config', $data);
	}	 
}
