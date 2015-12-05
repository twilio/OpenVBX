<?php
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 **/
	
class MY_ModelException extends Exception {}
class MY_ModelDuplicateException extends MY_ModelException {}

class MY_ModelLiteral
{
	protected $string;
	public function __construct($string)
	{
		$this->string = $string;
	}
	
	public function __toString()
	{
		return $this->string;
	}
}

/**
 * Class MY_Model
 * @property int $id
 * @property bool $updated
 * @property string $created
 * @property int $tenant_id
 */
class MY_Model extends Model
{
	public static $caching = true;
	protected static $__CLASS__ = __CLASS__;

	public $table = '';
	public $values = array();
	public $fields = array();
	public $admin_fields = array();
	
	/*
	 */
	public function __construct($object = null)
	{
		parent::__construct();

		if(!is_null($object))
		{
			foreach($this->fields as $property)
			{
				$this->values[$property] = isset($object->$property) ? $object->$property : null;
			}
		}
		
		$ci = &get_instance();
		if($ci->tenant && !isset($this->values['tenant_id']))
		{
			$this->tenant_id = $this->values['tenant_id'] = $ci->tenant->id;
			/* Persisted tenant should clobber the current tenant */
			if($object
			   && isset($object->tenant_id)
			   && $object->tenant_id
			   && $object->tenant_id != $this->tenant_id)
			{
				$this->tenant_id = $this->values['tenant_id'] = $object->tenant_id;
			}
		}
		
		if (!$ci->cache)
		{
			self::$caching = false;
		}
	}	   

	static function search($class,
						   $table,
						   $search_options,
						   $sql_options = array(),
						   $limit = -1,
						   $offset = 0)
	{
		$ci = &get_instance();		
		$tenant_id = $ci->tenant->id;

        $cached_objects_key = $class.'-'.md5(serialize($search_options).
     									serialize($sql_options).$limit.$offset);

		if (self::_caching($class))
		{
			// Check cache first
			if ($cached_keys = $ci->cache->get($cached_objects_key, $class, $tenant_id))
			{
				$cached_objects = array();
				foreach ($cached_keys as $object_cache_key)
				{
					if ($cached_object = $ci->cache->get($object_cache_key, $class, $tenant_id))
					{
						array_push($cached_objects, $cached_object);
					}
					else 
					{
						// we can't complete the list, so break out
						// and let the function continue
						unset($cached_objects);
						break;
					}
				}
	
				if (!empty($cached_objects))
				{
					if($limit == 1 && count($cached_objects) == 1)
					{
						return $cached_objects[0];
					}
					else
					{
						return $cached_objects;
					}
				}
			}
		}
		
		$joins = !empty($sql_options['joins'])? $sql_options['joins'] : array();
		$select = !empty($sql_options['select'])? $sql_options['select'] : array();
		
		if(empty($table))
		{
			throw new MY_ModelException('Table not set.');
		}

		if(is_string($search_options))
		{
			$search_options = array('id' => $search_options);
		}

		if(isset($search_options['id']))
		{
			$search_options["{$table}.id"] = $search_options['id'];
			unset($search_options['id']);
		}

		/* Tenantize */
		$ci = &get_instance();
		$search_options["{$table}.tenant_id"] = $ci->tenant->id;
		
		foreach($search_options as $option => $value)
		{
			if (preg_match('/([^_]+)__like_?(before|after|both)$/', $option, $side_match))
			{
				$side = empty($side_match[2])? 'both' : $side_match[2];
				$option = $side_match[1];
				$ci->db
					 ->like($option, $value, $side);
			}
			elseif (preg_match('/([^_]+)__(not_in|in)$/', $option, $matches))
			{
				list($comp, $key, $type) = $matches;
				$method = ($type == 'in' ? 'where_in' : 'where_not_in');
				$ci->db->$method($key, $value);
			}
			else
			{
				$ci->db->where($option, $value);
			}
		}
		
		$ci->db->from($table);
		
		if ($limit != -1)
		{
			$ci->db->limit($limit, $offset);
		}

		if(!empty($joins))
		{
			foreach($joins as $table => $condition)
			{
				$ci->db->join($table, $condition);
			}
		}

		if(!empty($select))
		{
			$ci->db->select(implode(', ', $select));
		}
		
		if (!empty($sql_options['order_by']))
		{
			if (is_array($sql_options['order_by']))
			{
				$ci->db->order_by($sql_options['order_by'][0], $sql_options['order_by'][1]);
			}
			else
			{
				$ci->db->order_by($sql_options['order_by']);
			}
		}

		/** @var CI_DB_Result $query */
		$query = $ci->db->get();
		
		$results = false;
		if (!empty($query))
		{
			$results = $query->result();
		
			foreach($results as $i => $result)
			{
				$results[$i] = new $class($result);
			}
				
			// cache results
			if (self::_caching($class))
			{
				$cached_object_ids = array();
				foreach ($results as $result)
				{
					array_push($cached_object_ids, $result->id);
					$ci->cache->set($result->id, $result, $class, $ci->tenant->id);
				}
				$ci->cache->set($cached_objects_key, $cached_object_ids, $class, $ci->tenant->id);
				reset($results);
			}
		}
		
		if($limit == 1 && count($results) == 1)
		{
			return $results[0];
		}
		else
		{
			return $results;
		}
	}
	
	function set_fields($params)
	{
		foreach($params as $key => $value)
		{
			if(isset($params[$key]))
			{
				$ci = &get_instance();
				if($value instanceof MY_ModelLiteral)
				{
					$ci->db->set($key, $value, false);
				}
				else
				{
					$ci->db->set($key, $value);
				}
			}
		}

		/* Tenantize */
		if(!in_array('tenant_id', array_keys($params)))
		{
			$ci->db->set("{$this->table}.tenant_id", $this->tenant_id);
		}
	}
	
	function update($id, $params)
	{
		if(empty($params))
		{
			return true;
		}
			
		$this->set_fields($params);
		$ci = &get_instance();
		if(is_array($id))
		{
			foreach($id as $key => $value)
			{
				$ci->db->where($key, $value);
			}

			/* Tenantize */
			$ci->db->where("{$this->table}.tenant_id", $this->tenant_id);
			$r = $ci->db->update($this->table);
		}
		else
		{
			$r = $ci->db
				->where('id', $id)
				->update($this->table);
		}
		
		$classname = get_class($this);
		if (self::_caching($classname))
		{
			$ci->cache->invalidate($classname, $this->tenant_id);
		}
		return $r;
	}
	
	function insert($params)
	{
		$ci = &get_instance();
		
		if(isset($this->unique) && !empty($this->unique))
		{
			$ci->db->from("{$this->table}");
			
			foreach($this->unique as $column)
			{
				$ci->db->where("{$this->table}.`{$column}`", isset($this->values[$column])? $this->values[$column] : '');
			}
			
			/* Tenantize */
			$ci->db->where("{$this->table}.tenant_id", $this->tenant_id);
			if(($result = count($ci->db->get()->result())) > 0)
			{
				throw new MY_ModelDuplicateException("Duplicate entry exists - $result");
			}
		}
		
		$this->set_fields($params);
		$ci->db
			 ->insert($this->table);
		$this->id = $ci->db->insert_id();
		
		$classname = get_class($this);
		if (self::_caching($classname))
		{
			$ci->cache->invalidate($classname, $this->tenant_id);
		}
	}

	function delete()
	{
		$ci = &get_instance();
		
		if(is_numeric($this->id) && intval($this->id) < 1)
		{
			if(!empty($this->natural_keys))
			{
				$delete = false;
				$where = array();
				foreach($this->natural_keys as $natural_key)
				{
					$delete = !empty($this->values[$natural_key]);
					if($delete)
					{
						$where[$natural_key] = $this->values[$natural_key];
					}
				}
				
				if($delete && !empty($where))
				{
					$ci = &get_instance();
					foreach($where as $key => $val)
					{
						$ci->db->where("{$this->table}.`{$key}`", $val);
					}
					
					/* Tenantize */
					$ci->db->where("{$this->table}.tenant_id", $this->tenant_id);
					$ci->db->delete($this->table);
					
					$classname = get_class($this);
					if (self::_caching($classname))
					{
						$ci->cache->delete($this->id, $classname, $this->tenant_id);
					}
					return true;
				}
			}

			throw new MY_ModelException('Unable to delete: No id specified');
		}

		$ci->db
			 ->where("{$this->table}.id", $this->id)
			 ->delete($this->table);
			
		$classname = get_class($this);
		if (self::_caching($classname))
		{
			$ci->cache->invalidate($classname, $this->tenant_id);
		}
	}

	/**
	 * @param bool|false $force_update
	 * @return bool
	 * @throws MY_ModelDuplicateException
	 */
	function save($force_update = false)
	{
		if(is_numeric($this->id) && intval($this->id) > 0)
		{
			$this->update($this->id, $this->values);
			return true;
		}

		if(!empty($this->natural_keys) && $force_update)
		{
			$update = false;
			$where = array();
			foreach($this->natural_keys as $natural_key)
			{
				$update = !empty($this->values[$natural_key]);
				if($update)
				{
					$where[$natural_key] = $this->values[$natural_key];
				}
			}
			
			if($update && !empty($where))
			{
				$this->update($where, $this->values);
				return true;
			}
		}

		$this->insert($this->values);
		
		$ci =& get_instance();
		$classname = get_class($this);
		if (self::_caching($classname))
		{
			$ci->cache->invalidate($classname, $this->tenant_id);
		}
		
		return true;
	}

	/**
	 * @param $name
	 * @return string|null
	 */
	function __get($name)
	{
		if(isset($this->values[$name]))
		{
			return $this->values[$name];
		}

		return null;
	}

	function __set($name, $value)
	{
		if(in_array($name, $this->fields))
		{
			$this->values[$name] = $value;
			return;
		}
		
		$this->$name = $value;
		return;
	}

	public static function __set_state($props) 
	{
		$obj = new self;
		foreach($props as $key => $value)
		{
			if(in_array($key, $obj->fields))
			{
				$obj->$key = $value;
			}
		}

		return $obj;
	}
   
	/**
	 * An unfortunate hack for scope resolution in PHP versions less than 5.3
	 * We have to always use the hack because PHP 5.2 will complain about $class::$var
	 * even if its hidden via an if statement
	 *
	 * @param string $class
	 * @return bool
	 */
	protected function _caching($class)
	{
		$_class = new ReflectionClass($class);
		$caching = $_class->getStaticPropertyValue('caching', self::$caching);

		return $caching;
	}
}
