<?php

abstract class OpenVBX_Cache_Abstract
{		
	protected $default_expires = 0;
	protected $default_group = '_default_';
			
	public function get($key, $group = null) 
	{
		return $this->_get($key, $group);
	}

	public function set($key, $data, $group = null, $expires = null)
	{
		return $this->_set($key, $data, $group, $expires);
	}
		
	public function delete($key, $group = null) 
	{
		return $this->_delete($key, $group);
	}

	public function group($group)
	{
		return $this->_group($group);
	}

	public function flush($group = null)
	{
		return $this->_flush($group);
	}
	
	public static function load() {
		$ci =& get_instance();
		$ci->config->load('cache');
		$settings = $ci->config->item('cache');
		
		$type = $settings['cache_type'];
		
		if ($settings['cache_type'] == 'auto-detect')
		{
			$type = self::auto_detect();
		}
		
		$options = array(
			'default_expires' => $settings['default_expires']
		);
		switch ($type)
		{
			case 'auto-detect':
				// @todo - detect capabilities, fall back if necessary
				break;
			case 'apc':
				include_once('Caches/APC.php');
				$class = 'OpenVBX_Cache_APC';
				break;
			case 'memcached':
				include_once('Caches/Memcached.php');
				$class = 'OpenVBX_Cache_Memcached';
				$options = $settings['memcached_settings'];
				break;
			case 'default':
			default:
				include_once('Caches/Local.php');
				$class = 'OpenVBX_Cache_Local';
		}
		
		return new $class($options);
	}
	
	public static function auto_detect()
	{
		$type = 'default';
		
		if (extension_loaded('memcache')) 
		{
			$type = 'memcached';
		}
		elseif (function_exists('apc_fetch'))
		{
			$type = 'apc';
		}
		
		return $type;
	}
	
	protected abstract function _get($key, $group = null);
	protected abstract function _set($key, $data, $group = null, $expires = null);
	protected abstract function _delete($key, $group = null);
	protected abstract function _group($group);
	protected abstract function _flush($group = null);
}