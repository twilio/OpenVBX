<?php

class OpenVBX_Cache_Local extends OpenVBX_Cache_Abstract
{	
	private $_cache;
	
	public function __construct($options) {
		parent::__construct($options);
	}
	
	public function __destruct() {}
	
	public function _set($key, $data, $group = null, $tenant_id,  $expires = null)
	{	
		if (empty($expires))
		{
			$expires = $this->default_expires;
		}
		
		$group = $this->_tenantize_group($group, $tenant_id);
		
		$ret = $this->_cache[$group][$key] = array(
			'data' => $data,
			'expires' => time() + intval($expires)
		);
				
		return $ret;
	}
	
	public function _get($key, $group = null, $tenant_id)
	{
		$data = false;
		
		$group = $this->_tenantize_group($group, $tenant_id);
		
		if (isset($this->_cache[$group][$key]))
		{
			if ($this->_cache[$group][$key]['expires'] > time())
			{
				$data = $this->_cache[$group][$key]['data'];
			}
			else
			{
				$this->_delete($key, $group);
			}
		}

		return $data;
	}
	
	public function _delete($key, $group = null, $tenant_id)
	{
		$group = $this->_tenantize_group($group, $tenant_id);
		
		if (isset($this->_cache[$group]) && isset($this->_cache[$group][$key]))
		{
			unset($this->_cache[$group][$key]);
			return true;
		}
		return false;
	}
	
	public function _flush()
	{
		$this->_cache = array();
	}
}