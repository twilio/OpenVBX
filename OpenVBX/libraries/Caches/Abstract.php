<?php

abstract class OpenVBX_Cache_Abstract
{	
	private $enabled = true;	
	protected $default_expires = 0;
	protected $default_group = '_default_';
			
	public function __construct($options)
	{	
		if (isset($options['cache_enabled']))
		{
			$this->enabled = (bool) $options['cache_enabled'];
		}
		if (isset($options['default_expires']))
		{
			$this->default_expires = $options['default_expires'];
		}
	}
		
	public function get($key, $group, $tenant_id) 
	{
		if (!$this->enabled)
		{
			return false;
		}
		return $this->_get($key, $group, $tenant_id);
	}

	public function set($key, $data, $group, $tenant_id, $expires = null)
	{
		if (!$this->enabled)
		{
			return false;
		}
		return $this->_set($key, $data, $group, $tenant_id, $expires);
	}
		
	public function delete($key, $group, $tenant_id) 
	{
		if (!$this->enabled)
		{
			return false;
		}
		return $this->_delete($key, $group, $tenant_id);
	}
	
	public function invalidate($group, $tenant_id)
	{		
		if (!$this->enabled)
		{
			return false;
		}
		return $this->_invalidate($group, $tenant_id);
	}

	public function flush()
	{
		if (!$this->enabled)
		{
			return false;
		}
		return $this->_flush();
	}
	
	public function enabled($enabled = null)
	{
		if (is_bool($enabled))
		{
			$this->enabled = $enabled;
		}
		return $this->enabled;
	}
	
	protected function _tenantize_group($group, $tenant_id)
	{
		if (empty($group))
		{
			$group = $this->default_group;
		}
		return $group.'-'.$tenant_id;
	}
	
	protected function _serialize($data)
	{
		if (!is_scalar($data))
		{
			$data = serialize($data);
		}
		return $data;
	}
	
	protected function _unserialize($data)
	{
		$ret = $data;
		$unserialized = unserialize($data);
		if ($unserialized !== false)
		{
			$ret = $unserialized;
		}
		return $ret;
	}
	
	public static function load($type = null) {
		$ci =& get_instance();
		$ci->config->load('cache');
		$settings = $ci->config->item('cache');
		
		$type = (!empty($type) ? $type : $settings['cache_type']);
				
		$options = array(
			'default_expires' => 3600,
			'cache_enabled' => true
		);

		// import settings to be passed to cache object
		foreach ($options as $key => $value)
		{
			if (isset($settings[$key]))
			{
				$options[$key] = $settings[$key];
			}
		}

		$basepath = APPPATH.'/libraries/Caches/';

		switch (true)
		{
			case $type == 'apc' && function_exists('apc_fetch'):
				require_once($basepath.'APC.php');
				$class = 'OpenVBX_Cache_APC';
				break;
			case $type == 'memcache' && class_exists('Memcache'):
				require_once($basepath.'Memcache.php');
				$class = 'OpenVBX_Cache_Memcache';
				$options = $settings['memcache'];
				break;
			case $type == 'db':
				require_once($basepath.'DB.php');
				$class = 'OpenVBX_Cache_DB';
				break;
			case 'default':
			default:
				require_once($basepath.'Local.php');
				$class = 'OpenVBX_Cache_Local';
		}

		return new $class($options);
	}
	
	protected abstract function _get($key, $group, $tenant_id);
	protected abstract function _set($key, $data, $group, $tenant_id, $expires = null);
	protected abstract function _delete($key, $group, $tenant_id);
	protected abstract function _invalidate($group, $tenant_id);
	protected abstract function _flush();
}
