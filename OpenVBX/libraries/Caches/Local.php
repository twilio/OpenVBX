<?php

class OpenVBX_Cache_Local extends OpenVBX_Cache_Abstract
{	
	protected $friendly_name = 'Local (memory)';
	protected $more_info = null;
	
	private $_cache;
	
	public function __construct($options) {
		parent::__construct($options);
	}
	
	public function __destruct() {}
		
	public function _set($key, $data, $group, $tenant_id,  $expires = null)
	{	
		if (empty($expires))
		{
			$expires = $this->default_expires;
		}
		
		$_group = $this->_tenantize_group($group, $tenant_id);
		
		$ret = $this->_cache[$_group][$key] = array(
			'data' => $data,
			'expires' => time() + intval($expires)
		);
				
		return $ret;
	}
	
	public function _get($key, $group, $tenant_id)
	{
		$data = false;
		
		$_group = $this->_tenantize_group($group, $tenant_id);
		
		if (isset($this->_cache[$_group][$key]))
		{
			if ($this->_cache[$_group][$key]['expires'] > time())
			{
				$data = $this->_cache[$_group][$key]['data'];
			}
			else
			{
				$this->_delete($key, $_group, $tenant_id);
			}
		}

		return $data;
	}
	
	public function _delete($key, $group, $tenant_id)
	{
		$_group = $this->_tenantize_group($group, $tenant_id);
		
		if (isset($this->_cache[$_group]) && isset($this->_cache[$_group][$key]))
		{
			unset($this->_cache[$_group][$key]);
			return true;
		}
		return false;
	}
	
	public function _invalidate($group, $tenant_id)
	{
		$_group = $this->_tenantize_group($group, $tenant_id);
		if (isset($this->_cache[$_group]))
		{
			unset($this->_cache[$_group]);
		}
		
		return true;
	}
	
	public function _flush()
	{
		$this->_cache = array();
	}
}