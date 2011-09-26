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
	
class VBX_DeviceException extends Exception {}
	
/*
 * Device class
 */
class VBX_Device extends MY_Model
{
	protected static $__CLASS__ = __CLASS__;
	public $table = 'numbers';

	static public $joins = array();
	static public $select = array('numbers.*');

	var $error_prefix = '';
	var $error_suffix = '';
	
	var $fields = array('id', 'name', 'value', 'sms', 'sequence', 'is_active', 'user_id');

	function __construct($object = null)
	{
		parent::__construct($object);
	}
	
	static function get($search_options = array(), $limit = -1, $offset = 0)
	{
		if(empty($search_options))
		{
			return null;
		}

		if(is_numeric($search_options))
		{
			$search_options = array('id' => $search_options);
		}
		
		return self::search($search_options,
							1,
							0);
	}

	static function search($search_options = array(), $limit = -1, $offset = 0)
	{
		$sql_options = array('joins' => self::$joins,
							 'select' => self::$select,
							 );
		$device = new self();
		
		$devices = parent::search(self::$__CLASS__,
								  $device->table,
								  $search_options,
								  $sql_options,
								  $limit,
								  $offset);

		return $devices;
	}

	function add($number)
	{
		if(empty($number['value'])
		   || empty($number['name'])
		   || empty($number['user_id']))
		{
			throw new VBX_DeviceException('Invalid number');
		}

		if($this->get_by_number($number['value'], $number['user_id']))
		{
			throw new VBX_DeviceException('Name and number already exist.');
		}

		try
		{
			PhoneNumber::validatePhoneNumber($number['value']);
		}
		catch(PhoneNumberException $e)
		{
			throw new VBX_DeviceException($e->getMessage());
		}

		$ci =& get_instance();
		
		if(!($ci->db
			 ->set('name', $number['name'])
			 ->set('value', normalize_phone_to_E164($number['value']))
			 ->set('user_id', $number['user_id'])
			 ->set('sms', $number['sms'])
			 ->set('tenant_id', $ci->tenant->id)
			 ->insert($this->table)))
		{
			throw new VBX_DeviceException('Name and number already exist.');
		}

		return $ci->db->insert_id();
	}

	function delete($id, $user_id)
	{
		$ci =& get_instance();

		$ci->db
			->where('id', $id)
			->where('user_id', $user_id)
			->where('tenant_id', $ci->tenant->id)
			->delete($this->table);

		if(!$ci->db
		   ->affected_rows())
		{
			throw new VBX_DeviceException('Nothing was deleted');
		}
	}

	function get_by_group($group_id)
	{
		$ci =& get_instance();

		$ci->db->where_in('user_id', Group::get_user_ids($group_id));
		$ci->db->where('tenant_id', $ci->tenant->id);
		$ci->db->from($this->table);
		$ci->db->order_by('sequence');
		return $ci->db->get()->result();
	}

	function get_by_user($user_id)
	{
		$ci =& get_instance();

		return $ci->db
			->from($this->table)
			->where('user_id', $user_id)
			->where('tenant_id', $ci->tenant->id)
			->order_by('sequence')
			->get()->result();
	}

	function get_by_number($number, $user_id)
	{
		$number = normalize_phone_to_E164($number);
		$ci =& get_instance();

		return $ci->db
			->from($this->table)
			->where('user_id', $user_id)
			->where('value', $number)
			->where('tenant_id', $ci->tenant->id)
			->get()->row();
	}

	function save()
	{
		if(!strlen($this->name))
		{
			throw new VBX_DeviceException('Name is empty');
		}
		
		try
		{
			PhoneNumber::validatePhoneNumber($this->value);
		}
		catch(PhoneNumberException $e)
		{
			throw new VBX_DeviceException($e->getMessage());
		}
		
		return parent::save();
	}
}