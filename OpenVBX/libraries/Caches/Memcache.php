<?php

class OpenVBX_Cache_Memcache extends OpenVBX_Cache_Abstract
{
	protected $friendly_name = 'Memcache';
	protected $more_info = 'http://php.net/memcache';
	
	private $_cache;
	
	private $flags = null;
	
	private $port = '11211';
	private $server = '127.0.0.1';
	private $extra_servers = null;
	
	public function __construct($options)
	{
		parent::__construct($options);
		
		if (!extension_loaded('memcache'))
		{
			log_message('error', 'Memcache extension not loaded. Disabling cache.');
			parent::enabled(false);
			return false;
		}
		
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
		if ($this->_cache->connect($this->server, $this->port))
		{
			if (method_exists($this->_cache, 'addServer') && !empty($this->extra_servers))
			{
				foreach ($this->extra_servers as $server)
				{
					$port = $this->port;

					// detect port, only supports IP addresses
					if (strpos($server, ':'))
					{
						list($server, $port) = explode(':', $server);
					}
				
					$this->_cache->addServer($server, $port);
				}
			}
		}
		else
		{
			parent::enabled(false);
			log_message('error', 'Could not connect to Memcache server. Disabling cache.');
		}
	}
	
	private function _generationalize($group, $tenant_id)
	{
		$_group = $this->_tenantize_group($group, $tenant_id);
		$_ggroup = $this->_generation_keyname($group, $tenant_id);
		if (!($generation = $this->_cache->get($_ggroup, $this->flags)))
		{
			$generation = 0;
		}
		return $_group.'_g'.$generation;
	}
	
	private function _keyname($key, $group, $tenant_id) {
		$_group = $this->_generationalize($group, $tenant_id);
		$_group = $this->_tenantize_group($_group, $tenant_id).'.'.$key;
		return $_group;
	}
	
	private function _generation_keyname($group, $tenant_id)
	{
		return $this->_tenantize_group($group, $tenant_id).'-generation';
	}
	
	protected function _get($key, $group, $tenant_id)
	{
		$_key = $this->_keyname($key, $group, $tenant_id);
		if ($data = $this->_cache->get($_key, $this->flags))
		{
			$data = $this->_unserialize($data);
		}
		
		return $data;
	}        
	         
	protected function _set($key, $data, $group, $tenant_id, $expires = null)
	{
		if (empty($expires))
		{
			$expires = $this->default_expires;
		}
		
		$_key = $this->_keyname($key, $group, $tenant_id);
				
		$_data = $this->_serialize($data); 
		return $this->_cache->set($_key, $_data, $this->flags, $expires);
	}        
	         
	protected function _delete($key, $group, $tenant_id)
	{        
		$_key = $this->_keyname($key, $group, $tenant_id);
		return $this->_cache->delete($key);
	}               
	
	public function _invalidate($group, $tenant_id)
	{
		$_ggroup = $this->_generation_keyname($group, $tenant_id);
		$generation = $this->_cache->increment($_ggroup);
		if ($generation === false)
		{
			$generation = 0;
			$this->_cache->set($_ggroup, 0);
		}
		return $generation;
	}
	  
	protected function _flush()
	{
		$this->_cache->flush();
	}
}