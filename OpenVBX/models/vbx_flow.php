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

class VBX_FlowException extends Exception {}

/**
 * Flow Class
 * @property int $id
 * @property string $name
 * @property string $data
 * @property string $sms_data
 * @property int $user_id
 */
class VBX_Flow extends MY_Model {

	static protected $__CLASS__ = __CLASS__;
	static public $select = array('flows.*');
	public $table = 'flows';
	public $numbers = array();
	public $fields = array('id', 'name', 'data', 'sms_data', 'user_id');
	public $unique = array('name');

	private $_instances = null;

	public function __construct($object = null)
	{
		parent::__construct($object);
	}

	/**
	 * @param array $search_options
	 * @param int $limit
	 * @param int $offset
	 * @return self
	 */
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

		return self::search($search_options, 1, 0);
	}

	static function nextId()
	{
		$obj = new self();
		$ci = &get_instance();
		$ci->db->select('id');
		$ci->db->from($obj->table);
		$ci->db->where('tenant_id', $ci->tenant->id);
		$sum = $ci->db->count_all_results();
		return $sum + 1;
	}
	
	static function count() {
		$obj = new self();
		$ci = &get_instance();
		$count = $ci->db->count_all_results($obj->table);
		if ($count > 0) {
			return $count;
		}
		return false;
	}

	/**
	 * @param array $search_options
	 * @param int $limit
	 * @param int $offset
	 * @return self|self[]
	 * @throws MY_ModelException
	 */
	static function search($search_options = array(), $limit = -1, $offset = 0)
	{
		$ci = &get_instance();

		$sql_options = array();

		$include_numbers = true;
		if(isset($search_options['numbers'])) {
			$include_numbers = $search_options['numbers'];
			unset($search_options['numbers']);
		}

		$obj = new self();
		$flows = parent::search(self::$__CLASS__,
								$obj->table,
								$search_options,
								$sql_options,
								$limit,
								$offset);

		if(is_object($flows))
		{
			$flows = array($flows);
		}

		if($include_numbers) 
		{
			try {
				$ci->load->model('vbx_incoming_numbers');
				$numbers = $ci->vbx_incoming_numbers->get_numbers();
				$flow_ids_to_numbers = array();

				foreach($numbers as $num)
				{
					if($num->installed)
					{
						$flow_ids_to_numbers[$num->flow_id][] = $num;
					}
				}

				foreach($flows as $flow)
				{
					$flow->numbers = array();
					$numbers_for_flow = array();

					if(array_key_exists(intval($flow->id), $flow_ids_to_numbers))
					{
						foreach($flow_ids_to_numbers[$flow->id] as $num)
						{
							$numbers_for_flow[] = $num->phone;
						}
					}

					$flow->numbers = $numbers_for_flow;
				}
			}
			catch(VBX_IncomingNumberException $e)
			{
				log_message($e->getMessage());
				$ci->session->set_flashdata('error', 'Unable to fetch incoming numbers '.
											'from Twilio');
			}
		}
		if($limit == 1 && count($flows) == 1)
		{
			$flows = $flows[0];
		}

		return $flows;
	}

	function save($force_update = false)
	{
		if(strlen($this->name) < 1)
		{
			/* Automatically name it - (total + 1) */
			$this->name = "Flow #".self::nextId();
		}

		try
		{
			// we also need to make sure that the flow store cache is nuked
			if (self::$caching)
			{
				$ci =& get_instance();
				$ci->cache->invalidate('VBX_Flow_Store', $ci->tenant->id);
			}
			return parent::save($force_update);
		}
		catch(MY_ModelDuplicateException $e)
		{
			throw new VBX_FlowException('A flow already exists by the name "'.
										$this->values['name'].'"');
		}
		catch(MY_ModelException $e)
		{
			throw new VBX_FlowException($e->getMessage());
		}
	}
}