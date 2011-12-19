<?php

class OpenVBX_Cache_APC extends OpenVBX_Cache_Abstract
{
	protected $friendly_name = 'APC';
	protected $more_info = 'http://php.net/apc';
	
	public function __construct($options)
	{
		parent::__construct($options);
		if (!extension_loaded('apc'))
		{
			$message = 'APC extension not loaded.';
			log_message('error', $message);
			parent::enabled(false, $message);
		}
	}
	
	private function _generationalize($group, $tenant_id)
	{
		$_group = $this->_tenantize_group($group, $tenant_id);
		$_ggroup = $this->_generation_keyname($group, $tenant_id);
		$generation = apc_fetch($_ggroup, $success);
		if (!$success)
		{
			$generation = 0;
		}
		return $_group.'_'.'g'.$generation;
	}
	
	private function _keyname($key, $group, $tenant_id) {
		$_group = $this->_generationalize($group, $tenant_id);
		$_group = $this->_tenantize_group($_group, $tenant_id);
		return $_group.'-'.$key;
	}
	
	private function _generation_keyname($group, $tenant_id)
	{
		return $this->_tenantize_group($group, $tenant_id).'-generation';
	}
	
	protected function _get($key, $group, $tenant_id)
	{
		$_key = $this->_keyname($key, $group, $tenant_id);
		$data = apc_fetch($_key, $success);

		if ($success)
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

		return apc_store($_key, $_data, $expires);
	}
	
	protected function _delete($key, $group, $tenant_id)
	{
		$_key = $this->_keyname($key, $group, $tenant_id);
		return apc_delete($_key);
	}
	
	protected function _invalidate($group, $tenant_id)
	{
		$_ggroup = $this->_generation_keyname($group, $tenant_id);
		$generation = apc_inc($_ggroup, 1, $success);
		if (!$success)
		{
			apc_store($_ggroup, 0);
			$generation = 0;
		}
		return $generation;
	}
		
	protected function _flush()
	{
		return apc_clear_cache('user');
	}
}