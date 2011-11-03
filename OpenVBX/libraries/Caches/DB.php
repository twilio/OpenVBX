<?php

class OpenVBX_Cache_DB extends OpenVBX_Cache_Abstract
{
	private $_db;
	private $_table = 'cache';
	
	public function __construct($options)
	{
		parent::__construct($options);
		$ci =& get_instance();
		$this->_db = $ci->db;
	}
	
	protected function _get($key, $group = null, $tenant_id)
	{
		$ret = false;
		
		$result = $this->_db
					->from($this->_table)
					->where('key', $key)
					->where('group', $group)
					->where('tenant_id', $tenant_id)
					->get()
					->result();

		if (!empty($result[0]))
		{
			$value = $this->_unserialize($result[0]->value);
			if ($value['expires'] > time())
			{
				$ret = $value['data'];
			}
			else
			{
				$this->_delete($key, $group, $tenant_id);
			}
		}

		return $ret;
	}
	
	protected function _set($key, $data, $group = null, $tenant_id, $expires = null)
	{
		if (empty($expires))
		{
			$expires = $this->default_expires;
		}

		$data = $this->_serialize(array(
			'data' => $data,
			'expires' => time() + intval($expires)
		));
		
		$this->_delete($key, $group, $tenant_id);
		
		$r = $this->_db
			->insert($this->_table, array(
				$this->_table.'.key' => $key,
				$this->_table.'.group' => $group,
				$this->_table.'.value' => $data,
				$this->_table.'.tenant_id' => $tenant_id
			));
				
		return $this->_db->affected_rows() > 0;
	}
	
	protected function _delete($key, $group = null, $tenant_id)
	{
		$this->_db
			->where('key', $key)
			->where('group', $group)
			->where('tenant_id', $tenant_id)
			->delete($this->_table);
		
		return true;
	}
	
	protected function _invalidate($group, $tenant_id)
	{
		// delete the group from the db
		$this->_db
			->where('group', $group)
			->where('tenant_id', $tenant_id)
			->delete($this->_table);
			
		return true;
	}
	
	protected function _flush()
	{
		$this->_db
			->truncate($this->_table);
	}
}