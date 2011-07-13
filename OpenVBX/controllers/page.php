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
	
class PageException extends Exception {}

class Page extends User_Controller
{
	public function __construct()
	{
		parent::__construct();
		
	}

	public function index()
	{
		$args = func_get_args();
		$page = implode('/', $args);

		$this->section = '/p/'.$page;
		$data = $this->init_view_data();

		$title = '';

		/* Find Plugin matching page */
		$plugins = Plugin::all();
		foreach($plugins as $plugin)
		{
			try
			{
				// First plugin wins
				$data['script'] = $plugin->getScript($page);
				if(!empty($data['script']))
				{
					PluginData::setPluginId($plugin->getPluginId());
					OpenVBX::$currentPlugin = $plugin;
					$plugin_info = $plugin->getInfo();

					$page_title = $plugin->getPluginPageName($page);
					$title = (!empty($page_title) ? $page_title : $plugin_info['name']);
					break;
				}
			}
			catch(PluginException $e)
			{
				error_log($e->getMessage());
				$ci = &get_instance();
				$ci->session->set_flashdata('error', $e->getMessage());
			}
		}
		
		$this->respond($title, 'page/index', $data);
	}
}
