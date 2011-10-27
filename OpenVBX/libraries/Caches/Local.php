<?php

class OpenVBX_Cache_Local extends OpenVBX_Cache_Abstract
{	
	private $_cache;
	
	public function __construct($options) {
		$this->default_expires = $options['default_expires'];
	}
	
	public function __destruct() {}
	
	public function _set($key, $data, $group = null, $expires = null)
	{
		if (empty($group))
		{
			$group = $this->default_group;
		}
		
		if (empty($expires))
		{
			$expires = $this->default_expires;
		}
		
		$ret = $this->_cache[$group][$key] = array(
			'data' => $data,
			'expires' => time() + intval($expires)
		);
				
		return $ret;
	}
	
	public function _get($key, $group = null)
	{
		$data = false;
		
		if (empty($group))
		{
			$group = $this->default_group;
		}
		
		if (isset($this->_cache[$group][$key]))
		{
			if ($this->_cache[$group][$key]['expires'] > time())
			{
				ep('cache hit for '.$key.'::'.$group);
				$data = $this->_cache[$group][$key]['data'];
			}
		}

		return $data;
	}
	
	public function _delete($key, $group = null)
	{
		if (empty($group)) 
		{
			$group = $this->default_group;
		}
		
		if (isset($this->_cache[$group]) && isset($this->_cache[$group][$key]))
		{
			unset($this->_cache[$group][$key]);
			return true;
		}
		return false;
	}
	
	public function _group($group)
	{
		if (isset($this->_cache[$group]))
		{
			$data = array();
			foreach ($this->_cache[$group] as $name => $item)
			{
				if ($item['expires'] > time())
				{
					$data[$name] = $item['data'];
				}
			}
			return $data;
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