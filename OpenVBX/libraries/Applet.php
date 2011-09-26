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
	
class AppletException extends Exception {}

// this is the base applet
class Applet
{
	public $id;					// the type of the applet equal to the directory name
	public $flow_id;
	public $flow_type;          // Current flow type
	
	public $applet_dir;
	public $currentURI;			// when outputing TwiML, this may be used to create sub-links; see "listen" applet

	// In child classes, these are overridden
	public $name = 'Base Applet';
	public $description = 'This is the base applet';
	public $order = 99;
	
	public static $flow_data;		// shared reference to the current flow instance data (output of $flow->get_instances())
	public $visible = TRUE;			// only used to hide the start applet from the toolbox

	protected $data = array();					// holds the instance variables

	protected $applet_repeated = FALSE;	 // Used to decipher if an applet has been repeated from last request

	private static $requiredFiles = array('ui.php', 'twiml.php', 'applet.json'); // These must exist for an applet to function properly

	public function __construct($config = null)
	{
		if($config)
		{
			$this->name = $config->name;
			$this->voice_name = !empty($config->voice_name)? $config->voice_name : $config->name;
			$this->sms_name = !empty($config->sms_name)? $config->sms_name : $config->name;
			$this->voice_title = !empty($config->voice_title)? $config->voice_title : $config->name;
			$this->sms_title = !empty($config->sms_title)? $config->sms_title : $config->name;
			$this->order = isset($config->order)? intval($config->order) : 99;
			$this->description = $config->description;
			$this->disabled = isset($config->disabled)? $config->disabled : false;
			$this->version = isset($config->version)? $config->version : false;
			
			if(!isset($config->type))
			{
				throw new AppletException("Applet missing type configuration: $config->name");
			}
			
			$this->type = is_string($config->type)? array($config->type) : $config->type;
			if(isset($config->visible))
			{
				$this->visible = (bool)$config->visible;
			}
		}
	}
	
	protected function set_data($data) {
		if(is_object($data)) $data = get_object_vars($data);
		if(is_array($data)) $this->data =& $data;
	}

	public function get_script()
	{
		return $this->get_file($this->script_file);
	}
	
	public function get_style()
	{
		return $this->get_file($this->style_file);
	}

	public function output_icon()
	{
		$name = 'icon.png';
		header("Content-type: image/png");
		$cache_time = time() + 3600;
		header("Expires: " . gmdate("D, d M Y H:i:s", $cache_time) . " GMT");

		@readfile((file_exists($this->applet_dir . $name) ? $this->applet_dir : PLUGIN_PATH) . $name);
		exit(0);
	}

	private function get_file($file)
	{
		if(file_exists($file))
		{
			return file_get_contents($file);
		}
		
		return FALSE;
	}

	// get all applet objects in the applet directory
	public static function get_applets($flow_type = 'voice')
	{
		$applets = array();
		$applet_names_by_plugin = array();
		
		$plugin_dir_names = scandir(PLUGIN_PATH);
		foreach($plugin_dir_names as $plugin_dir_name)
		{
			// Ignore current pwd 
			if($plugin_dir_name[0] == '.')
			{
				continue;
			}
			
			// Add to applet collection if contains an applets dir
			$applets_path = PLUGIN_PATH . '/' . $plugin_dir_name . '/applets';
			if(is_dir($applets_path))
			{
				$applet_names_by_plugin[$plugin_dir_name] = scandir($applets_path);
			}
		}

		// Iterate each applet to check for valid structure and create an instance
		foreach($applet_names_by_plugin as $plugin_dir_name => $applet_dir_names)
		{
			foreach($applet_dir_names as $applet_dir_name)
			{
				// Ignore current pwd 
				if($applet_dir_name[0] == '.')
				{
					continue;
				}
				
				try
				{
					$applet = null;
				
					$applet_path = PLUGIN_PATH . '/'. $plugin_dir_name . '/applets/' . $applet_dir_name;
				
					// Sanity check and make sure this path is accessible
					if(!is_dir($applet_path))
					{
						throw new AppletException("Applet path inaccessible $applet_path");
					}
				
					// Check for required files 
					$required_min = count(self::$requiredFiles);
					$files = scandir($applet_path);
				
					// If we have the minimum number of required files, lets move on.
					$required = array_intersect(self::$requiredFiles, $files);
					if(count($required) < $required_min)
					{
						throw new AppletException("Missing a required file, found: "
												  . implode(', ', $files)
												  . "\nRequired: "
												  . implode(', ', self::$requiredFiles) );
					}
				
					/* Process configuration */
					$applet_config = file_get_contents($applet_path . '/applet.json');
					$applet_config = json_decode($applet_config);

					if(is_null($applet_config))
					{
						throw new AppletException("Syntax error in applet.json");
					}
					
					/* Build Applet Instance */
					$applet = Applet::get($plugin_dir_name,
										  $applet_dir_name,
										  $applet_config);
					
					$applet->flow_type = $flow_type;
					
					if(is_null($applet))
					{
						throw new AppletException("Failed to create instance of applet");
					}

					$applets[$applet->id] = $applet;
				}
				catch(AppletException $e)
				{
					// Your applet developer has failed you at this point
					$message = "An error occurred loading applet: $applet_dir_name from $plugin_dir_name";
				
					// Notify the user
					$ci = &get_instance();
					$ci->session->set_userdata('error', $message);
				
					// Next applet in the list
					continue;
				}
			}
		}
		
		uasort($applets, array("Applet", "applet_sort"));
		return $applets;
	}

	public static function applet_sort($a, $b)
	{
		if($a->order == $b->order)
		{
			return 0;
		}

		return ($a->order < $b->order) ? -1 : 1;
	}

	public static function get($plugin_dir_name,
							   $applet_dir_name,
							   $applet_config = null,
							   $data = null)
	{
		$rel_plugin_path = '/plugins/' . $plugin_dir_name;
		$rel_applet_path = $rel_plugin_path . '/applets/'. $applet_dir_name;
		$plugin_path = PLUGIN_PATH . '/' . $plugin_dir_name;
		$applet_path = PLUGIN_PATH . '/' . $plugin_dir_name . '/applets/'. $applet_dir_name;
		
		if(!is_dir($applet_path))
		{
			throw new AppletException("Applet_path is inaccessible: $applet_path");
		}

		if(!is_object($applet_config))
		{
			$applet_config = file_get_contents($applet_path . '/applet.json');
			$applet_config = json_decode($applet_config);
			if(!is_object($applet_config))
			{
				throw new AppletException("Configuration is not an object");
			}
		}

		if(strpos($plugin_dir_name, "---"))
		{
			throw new AppletException("Illegel character sequence --- for plugin directory name: $plugin_dir_name");
		}
		
		if(strpos($applet_dir_name, "---"))
		{
			throw new AppletException("Illegel character sequence --- for applet directory name: $applet_dir_name");
		}

		$dir_name_regex = "[^a-zA-Z0-9-_]";
		if(preg_match($dir_name_regex, $plugin_dir_name) > 0)
		{
			throw new AppletException("Illegel character sequence $dir_name_regex for plugin directory name: $plugin_dir_name");
		}

		if(preg_match($dir_name_regex, $applet_dir_name) > 0)
		{
			throw new AppletException("Illegel character sequence $dir_name_regex for applet directory name: $applet_dir_name");
		}
		
		$object = new self($applet_config);
		$object->id = $plugin_dir_name . '---' . $applet_dir_name;
		$object->plugin_dir_name = $plugin_dir_name;
		$object->applet_dir_name = $applet_dir_name;
		$object->css_class_name = empty($applet_config->css_class_name)? $applet_dir_name : $applet_config->css_class_name;
		$object->plugin_path = PLUGIN_PATH . '/' . $plugin_dir_name;
		$object->icon_url = asset_url( $rel_applet_path . '/icon.png');
		$object->icon_file = $object->plugin_path .'/applets/'. $applet_dir_name . '/icon.png';
		if(!is_file( $object->icon_file )) {
			$object->icon_file = null;
			$object->icon_url = asset_url( 'assets/i/icon.png' );
		}
		
		$object->style_url = "";
		
		if(is_file( $applet_path . '/style.css' ))
		{
			// We'll use add_css to add the css to the page, and add_css expects relative URLs
			$object->style_url = $rel_applet_path . '/style.css';
		}
		$object->style_file = $applet_path . '/style.css';
		
		$object->script_url = "";
		if(is_file( $applet_path . '/script.js' ))
		{
			// We'll use add_js later to add this to the page, and add_js expects absolute URLs
			$object->script_url = asset_url( $rel_applet_path . '/script.js');
		}
		$object->script_file = $applet_path . '/script.js';
		
		$object->applet_dir = $applet_path;
		$object->data = $data;
		$object->description;
		return $object;
	}

	public function render($flow_id, $instance = null)
	{
		$path = $this->applet_dir . '/ui.php';
		if(!is_null($instance))
		{
			AppletInstance::setInstance($instance);
			$instance = isset($instance->data) && is_array($instance->data)? $instance->data : array();
		}
		else
		{
			$instance = isset($this->data) && is_array($this->data)? $this->data : array();
		}

		AppletInstance::setFlowType($this->flow_type);
		// Plugin directory name is the natural key until a proper guid system is developed
		$plugin = new Plugin($this->plugin_dir_name);
		PluginData::setPluginId($plugin->getPluginId());
		OpenVBX::$currentPlugin = $plugin;

		// Set the flow store singleton to current flow
		FlowStore::setFlowId($flow_id);
		if(!file_exists($path))
		{
			return '';
		}
		
		ob_start();
		include($path);
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	public function twiml($flow, $baseURI, $instance = null)
	{
		$path = $this->applet_dir . '/twiml.php';
		if(!is_null($instance))
		{
			AppletInstance::setInstance($instance);
			AppletInstance::setFlow($flow);
			AppletInstance::setBaseURI($baseURI);
			FlowStore::setFlowId($flow->id);
			// Plugin directory name is the natural key until a proper guid system is developed
			$plugin = new Plugin($this->plugin_dir_name);
			PluginData::setPluginId($plugin->getPluginId());
			OpenVBX::$currentPlugin = $plugin;

			$instance = isset($instance->data) && is_array($instance->data)? $instance->data : array();
		}
		else
		{
			$instance = isset($this->data) && is_array($this->data)? $this->data : array();
		}
		
		AppletInstance::setFlowType($this->flow_type);

		if(!file_exists($path))
		{
			return '';
		}

		$output = '<?xml version="1.0" ?><Response />';
		ob_start();
		require_once(APPPATH.'libraries/twilio.php');

		require_once($path);
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	public function tropoJSON($flow, $baseURI, $instance = null)
	{
		$path = $this->applet_dir . '/tropo.php';
		if(!is_null($instance))
		{
			AppletInstance::setInstance($instance);
			AppletInstance::setFlow($flow);
			AppletInstance::setBaseURI($baseURI);
			FlowStore::setFlowId($flow->id);
			// Plugin directory name is the natural key until a proper guid system is developed
			$plugin = new Plugin($this->plugin_dir_name);
			PluginData::setPluginId($plugin->getPluginId());
			OpenVBX::$currentPlugin = $plugin;

			$instance = isset($instance->data) && is_array($instance->data)? $instance->data : array();
		}
		else
		{
			$instance = isset($this->data) && is_array($this->data)? $this->data : array();
		}
		
		AppletInstance::setFlowType($this->flow_type);

		if(!file_exists($path))
		{
			return '';
		}

		ob_start();
		require_once(APPPATH.'libraries/tropo/tropo.class.php');

		require_once($path);
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
	
}	// END: applet classs