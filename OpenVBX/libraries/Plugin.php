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
	
class PluginException extends Exception {}

class Plugin
{
	// Parent directory where this plugin is installed
	protected $plugins_dir;
	// Name of directory where plugin is installed
	protected $dir_name;

	// Full directory path to plugin
	protected $plugin_path;
	
	// Plugin configuration for plugin.json 
	protected $config;

	public function __construct($plugin_dir_name = null, $plugins_dir = null)
	{
		if(empty($plugin_dir_name))
		{
			throw new PluginException('Empty plugin directory name');
		}

		if(empty($plugins_dir))
		{
			$plugins_dir = PLUGIN_PATH;
		}
		
		$this->plugins_dir = $plugins_dir;
		$this->dir_name = $plugin_dir_name;
		$this->plugin_path = $this->plugins_dir . '/' . $this->dir_name;
		if(is_file($this->plugin_path . '/plugin.json'))
		{
			$config_contents = file_get_contents($this->plugin_path . '/plugin.json');
			if(!empty($config_contents))
			{
				$this->config = json_decode($config_contents);
			}

			/* Optional arguments should be checked and declared here */
			$this->config->disabled = isset($this->config->disabled)? $this->config->disabled : false;
			$this->config->name = isset($this->config->name)? $this->config->name : 'Unknown';
			$this->config->author = isset($this->config->author)? $this->config->author : 'Unknown';
			$this->config->description = isset($this->config->description)? $this->config->description : '';
			$this->config->url = isset($this->config->url)? $this->config->url : '';
			$this->config->version = isset($this->config->version)? $this->config->version : '';
		}
		/* Gracefully handle plugins without a configuration file */
		else
		{
			$this->config = new stdClass();
			$this->config->name = $this->dir_name;
			$this->config->author = 'Unknown';
			$this->config->description = '';
			$this->config->url = '';
			$this->config->disabled = false;
			$this->config->version = false;
			
			/* Warn developers */
			error_log('Plugin is missing plugin.json file: '. $this->dir_name);
			
		}
	}

	public function getPluginId()
	{
		return 'PL'.md5($this->config->name);
	}

	public function getPluginVersion()
	{
		return $this->config->version;
	}

	public function getInfo()
	{
		$public_info = array('name' => $this->config->name,
							 'dir_name' => $this->dir_name,
							 'plugin_path' => $this->plugin_path,
							 'plugin_id' => $this->getPluginId()
							 );
		$config = get_object_vars($this->config);
		return array_merge($config, $public_info);
	}

	public function getScript($page)
	{
		if(empty($this->config))
		{
			throw new PluginException("Plugin has invalid configuration: $this->dir_name");
		}

		if($page == 'config')
		{
			$script = $this->plugin_path . '/config.php';
			if(!is_file($script))
			{
				throw new PluginException("Missing file in plugin: $this->dir_name :: config.php");
			}
			return $script;
		}
		
		if(!empty($this->config->links))
		{
			$found = false;
			foreach($this->config->links as $index => $link)
			{
				if($link->url == $page)
				{
					$found = true;
					break;
				}
			}
			
			if(isset($this->config->links[$index]) && $found)
			{
				$page = $this->config->links[$index];
				$script = $this->plugin_path . '/' . $page->script;
				if(!is_file($script))
				{
					throw new PluginException("Missing file in plugin: $this->dir_name :: $page->script");
				}
				
				return $script;
			}
		}
	}

	public function getHookScript($page)
	{
		if(empty($this->config))
		{
			throw new PluginException("Plugin has invalid configuration: $this->dir_name");
		}

		if($page == 'config')
		{
			return false;
		}

		if(!empty($this->config->links))
		{
			$found = false;
			foreach($this->config->links as $index => $link)
			{
				if($link->hook == true AND $link->url == $page)
				{
					$found = true;
					break;
				}
			}

			if(isset($this->config->links[$index]) && $found)
			{
				$page = $this->config->links[$index];
				$script = $this->plugin_path . '/' . $page->script;
				if(!is_file($script))
				{
					throw new PluginException("Missing file in plugin: $this->dir_name :: $page->script");
				}

				return $script;
			}
		}
	}
	
	public function getPluginPageName($page) {
		if(empty($this->config)) {
			throw new PluginException("Plugin has invalid configuration: $this->dir_name");
		}
		
		$name = '';
		if (empty($this->config->links) || $this->config->disabled) {
			return $name;
		}
		
		foreach ($this->config->links as $link) {
			if (!empty($link->url) && $link->url == $page && empty($link->hook)) {
				$name = $link->label;
			}
		}
		
		return $name;
	}

	public function getLinks()
	{
		$nav = array();
		
		if(empty($this->config))
		{
			throw new PluginException("Plugin has invalid configuration: $this->dir_name");
		}

		if(empty($this->config->links) || $this->config->disabled)
			return array();

		// HACK: Put this in a better spot, Menu class?
		$standard_menu_options = array('util', 'admin', 'setup', 'site_admin', 'log');
		
		foreach($this->config->links as $link)
		{
			// don't expose API/Ajax hooks to the menu
			if (!empty($link->hook)) {
				continue;
			}
			
			$script = isset($link->script)? $link->script : '';
			$url = isset($link->url)? $link->url : '';
			$label = isset($link->label)? $link->label : '';
			$menu = isset($link->menu)? $link->menu : '';

			if(empty($url))
			{
				/* throw exception? */
			}
			
			if(empty($menu))
			{
				/* throw exception? */
			}

			if(empty($label))
			{
				/* throw exception? */
			}
			
			if(empty($script))
			{
				/* throw exception? */
			}

			if(in_array($menu, $standard_menu_options))
			{
				$nav[$menu.'_links']["p/$url"] = $label;
			}
			else
			{
				$nav['plugin_menus'][strtolower($menu)]["p/$url"] = $label;
			}
		}
		
		return $nav;
	}

	public static function get($name)
	{
		try
		{
			return new self($name, PLUGIN_PATH);
		}
		catch(PluginException $e)
		{
			error_log($e->getMessage());
			$ci = &get_instance();
			$ci->session->set_flashdata('error', 'Failed to initialize plugin: '.$e->getMessage());
		}

		return null;
	}

	/**
	 * @return Plugin[]
	 */
	public static function all()
	{
		$plugins = array();
		$plugin_dir_names = scandir(PLUGIN_PATH);
		foreach($plugin_dir_names as $plugin_dir_name)
		{
			// Ignore hidden dirs
			if($plugin_dir_name[0] == '.')
			{
				continue;
			}
			
			try
			{
				$plugins[] = new self($plugin_dir_name, PLUGIN_PATH);
			}
			catch(PluginException $e)
			{
				error_log($e->getMessage());
				$ci = &get_instance();
				$ci->session->set_flashdata('error', 'Failed to initialize plugin: '.$e->getMessage());
			}
		}
		
		return $plugins;
	}
}
