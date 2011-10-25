<?php

class OpenVBX_Cache_Local implements OpenVBX_Cache_Abstract
{	
	public function __construct() {}
	public function __destruct() {}
	
	public function _set($key, $data, $group = 'default', $expires)
	{
		return $this->_cache[$group][$key] = array(
			'data' => $data,
			'expires' => time() + intval($expires)
		);
	}
	
	public function _get($key, $group = 'default')
	{
		$data = false;
		
		if (empty($group))
		{
			$group = 'default';
		}
		
		if (isset($this->_cache[$group][$key]))
		{
			if ($this->_cache[$group][$key]['expires'] > time())
			{
				$data = $this->_cache[$group][$key]['data'];
			}
		}
		
		return $data;
	}
	
	public function _delete($key, $group)
	{
		if (isset($this->_cache[$group]) && isset($this->_cache[$group][$key]))
		{
			unset($this->_cache[$group][$key]);
			return true;
		}
		return false;
	}
	
	public function _flush($group = null)
	{
		if (empty($group))
		{
			$this->_cache = array();
		}
		elseif (isset($this->_cache[$group]))
		{
			$this->_cache[$group] = array();
		}
	}
}