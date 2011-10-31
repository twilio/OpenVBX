<?php

class OpenVBX_Cache_APC extends OpenVBX_Cache_Abstract
{
	public function __construct($options)
	{
		parent::__construct($options);
	}
	
	private function _keyname($key, $group, $tenant_id) {
		return $this->_tenantize_group($group, $tenant_id).'.'.$key;
	}
	
	protected function _get($key, $group = null, $tenant_id)
	{
		$_key = $this->_keyname($key, $group);
		$data = apc_fetch($_key);
		
		if ($data !== false)
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
		return apc_store($_key, $_data, $expires);
	}
	
	protected function _delete($key, $group = null, $tenant_id)
	{
		$_key = $this->_keyname($key, $group, $tenant_id);
		return apc_delete($_key);
	}
		
	protected function _flush()
	{
		return apc_clear_cache('user');
	}
}