<?php

abstract class OpenVBX_Cache_Abstract
{
	private $_cache;
			
	public function get($key, $group) 
	{
		return $this->_get($key, $group);
	}

	public function set($key, $data, $group = 'default', $expires = '')
	{
		return $this->_set($key, $data, $group, $expires);
	}
		
	public function delete($key, $group) 
	{
		
	}


	public function flush()
	{
		
	}
	
	public static function load() {
		$ci =& get_instance();
		$ci->config->load('object-cache');
		$settings = $ci->config->item('object-cache');
		
		$type = $settings['cache_type'];
		
		if ($settings['cache_type'] == 'auto-detect')
		{
			$type = self::auto_detect();
		}
		
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
				$class = 'OpenVBX_Cache_Default';
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
	
	protected abstract function _get($key, $group = 'default');
	protected abstract function _set($key, $data, $group = 'default', $expires = '');
	protected abstract function _delete($key, $group = 'default');
	protected abstract function _flush($group);
}