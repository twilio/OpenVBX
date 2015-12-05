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
	
class VBX_GroupException extends Exception {}

/**
 * Class VBX_Group
 * @property int $id
 * @property string $name
 * @property int $is_active
 */
class VBX_Group extends MY_Model {

	protected static $__CLASS__ = __CLASS__;
	public $table = 'groups';
	
	static public $select = array('groups.*');
	
	public $fields =  array(
						'id', 
						'name', 
						'is_active'
					);
	
	public $admin_fields = array('');

	public function __construct($object = null)
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
			$search_options = array('id' => $search_options, 'is_active' => 1);
		}

		return self::search($search_options, 1, 0);
	}

	static function search($search_options = array(), $limit = -1, $offset = 0)
	{
		$sql_options = array(
			'joins' => array(),
			'select' => self::$select,
		);
		
		$obj = new self();
		$groups = parent::search(
			self::$__CLASS__,
			$obj->table,
			$search_options,
			$sql_options,
			$limit,
			$offset
		);
		
		if(is_object($groups))
		{
			$groups = array($groups);
		}
		
		$sorted_groups = array();
		foreach($groups as $group)
		{
			$sorted_groups[$group->id] = $group;
			$sorted_groups[$group->id]->users = array();
		}
		
		$groups = $sorted_groups;

		if(empty($sorted_groups))
		{
			return $sorted_groups;
		}
		
		$ci = &get_instance();
		$ci->db
			 ->select('u.*, g.*, gu.*')
			 ->from('groups as g')
			 ->join('groups_users gu', 'gu.group_id = g.id')
			 ->join('users u', 'u.id = gu.user_id')
			 ->where('u.is_active', true)
			 ->where_in('g.id', array_keys($sorted_groups))
			 ->where('g.is_active', true)
			 ->order_by('gu.order', 'asc');
		
		$groups_users = $ci->db->get()->result();
		foreach($groups_users as $gu)
		{
			$groups[$gu->group_id]->users[$gu->user_id] = $gu; 
		}
		
		if($limit == 1 && count($groups) > 0)
		{
			$groups = current($groups);
		}
		
		return $groups;
	}

	// --------------------------------------------------------------------

	static function get_user_ids($group_id)
	{
		$ci =& get_instance();

		$user_ids = array();
		$ci->db->select('gu.user_id');
		$ci->db->from('groups_users gu');
		$ci->db->join('users u', 'u.id = gu.user_id');
		$ci->db->where('gu.group_id', $group_id);
		$ci->db->where('u.is_active', true);
		$ci->db->group_by('gu.user_id');
		$ci->db->order_by('gu.order', 'asc');
		$users = $ci->db->get()->result();
		foreach($users as $gu) {
			$user_ids[] = $gu->user_id;
		}
		return $user_ids;
	}

	function get_by_id($group_id)
	{
		return self::get($group_id);
	}

	function add_user($user_id)
	{
		$ci =& get_instance();

		// get last increment id for group
		$last = $ci->db
				->select_max('order', 'max_order')
				->where('group_id', $this->id)
				->get('groups_users')
				->result();

		return $ci->db
			->set('user_id', $user_id)
			->set('group_id', $this->id)
			->set('tenant_id', $ci->tenant->id)
			->set('order', $last[0]->max_order+1)
			->insert('groups_users');
	}
	
	function remove_user($user_id)
	{
		$ci =& get_instance();

		$ci->db
			->from('groups_users as gu')
			->where('user_id', $user_id)
			->where('tenant_id', $ci->tenant->id)
			->where('group_id', $this->id);
		
		$result = $ci->db->delete('groups_users');		
		return $result;
	}
	
	function order_group($order) {
		if (empty($order)) {
			return false;
		}
		
		$ci =& get_instance();
		$ci->db->trans_begin();
		
		foreach ($order as $i => $user_id) 
		{
			$ci->db
				->where('user_id', $user_id)
				->where('group_id', $this->id)
				->update('groups_users', array(
						'order' => $i
					));
		}
		
		if ($ci->db->trans_status === false) 
		{
			$ci->db->trans_rollback();
			throw new VBX_GroupException('Could not update group order: '.
											$ci->db->_error_message());
		}
		else 
		{
			$ci->db->trans_commit();
		}
	}

	/**
	 * Soft delete
	 *
	 * @return void
	 */
	function delete()
	{
		$this->remove_all_users($this->id);
		$this->set_active($this->id, false);
		
		if (self::$caching)
		{
			$ci =& get_instance();
			$ci->cache->invalidate(__CLASS__, $ci->tenant->id);
		}
	}

	function remove_all_users($group_id)
	{
		$ci =& get_instance();

		$ci->db
			->where('tenant_id', $ci->tenant->id)
			->where('group_id', $group_id);
		
		$result = $ci->db->delete('groups_users');
		return $result;
	}

	function get_active_groups()
	{
		$search_opts = array(
			'is_active' => 1
		);
		return self::search($search_opts);
	}

	function set_active($id, $active = true)
	{
		$ci =& get_instance();

		return $ci->db
			->where('id', $id)
			->where('tenant_id', $ci->tenant->id)
			->set('is_active', $active)
			->update('groups');
	}

	public function save()
	{
		if(strlen($this->name) < 3)
		{
			throw new VBX_GroupException('Group name must be at least 3 characters long');
		}
		parent::save();
	}
}