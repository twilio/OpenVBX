<?php

class OpenVBX_Cache_Memcache extends OpenVBX_Cache_Abstract
{
	private $_cache;
	
	private $flags = null;
	
	private $port = '11211';
	private $server = '127.0.0.1';
	private $extra_servers = null;
	
	public function __construct($options)
	{
		parent::__construct($options);
				
		$this->_cache = new Memcache;
		
		if (!empty($options['memcache']['servers']))
		{
			$this->server = array_pop($options['memcache']['servers']);
			if (count($options['memcache']['servers']))
			{
				$this->extra_servers = $options['memcache']['servers'];
			}
		}
		
		if (!empty($options['memcache']['port']))
		{
			$this->port = $options['memcache']['port'];
		}
		
		$this->_connect();
	}
	
	private function _connect()
	{
		$this->_cache->connect($this->server, $this->port);
		if (method_exists($this->_cache, 'addServer') && !empty($this->extra_servers))
		{
			foreach ($this->extra_servers as $server)
			{
				$port = $this->port;

				// detect port, only supports IP addresses, but I think 
				// memcache only supports ip addresses anyway
				if (strpos($server, ':'))
				{
					list($server, $port) = explode(':', $server);
				}
				
				$memcache->addServer($server, $port);
			}
		}
	}
	
	private function _keyname($key, $group, $tenant_id) {
		return $this->_tenantize_group($group, $tenant_id).'.'.$key;
	}
	
	protected function _get($key, $group = null, $tenant_id)
	{
		$_key = $this->_keyname($key, $group, $tenant_id);
		if ($data = $this->_cache->get($_key, $this->flags))
		{
			$data = $this->_unserialize($data);
		}
		
		return $data;
	}        
	         
	protected function _set($key, $data, $group = null, $tenant_id, $expires = null)
	{
		if (empty($expires))
		{
			$expires = $this->default_expires;
		}
		
		$_key = $this->_keyname($key, $group, $tenant_id);
				
		$_data = $this->_serialize($data); 
		return $this->_cache->set($_key, $_data, $this->flags, $expires);
	}        
	         
	protected function _delete($key, $group = null, $tenant_id)
	{        
		$_key = $this->_keyname($key, $group, $tenant_id);
		return $this->_cache->delete($key);
	}               
	         
	protected function _flush()
	{
		$this->_cache->flush();
	}
}