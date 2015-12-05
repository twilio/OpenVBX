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

 /**
  * Rest Access Class
  * @property string $key
  * @property int $locked
  * @property int $user_id
  * @property int $id
  */
class VBX_Rest_access extends MY_Model
{
	protected static $__CLASS__ = __CLASS__;

	public $fields = array('created', 'key', 'locked', 'user_id', 'id');
	public $table = 'rest_access';
	
	public function make_key($user_id)
	{
		$ci =& get_instance();

		$key = md5(rand(10000, 99999).date('mdyhisj'));
		$ci->db
			->set('created', 'UTC_TIMESTAMP()', false)
			->set('`key`', $key)
			->set('locked', 0)
			->set('user_id', $user_id)
			->set('tenant_id', $ci->tenant->id)
			->insert('rest_access');

		return $key;
	}

	public function auth_key($key)
	{
		$ci =& get_instance();

		$user_id = 0;
		$results = $ci->db
			 ->where(array('key' => $key,
						   'locked' => 0,
						   'tenant_id' => $ci->tenant->id))
			 ->select( 'user_id' )
			 ->get('rest_access', 1)
			 ->result_array();
		if(!empty($results[0]['user_id']))
		{
			$user_id = $results[0]['user_id'];
			$ci->db->where(
							 array('key' => $key,
								   'locked' => 0,
								   'user_id' => $user_id,
								   'tenant_id' => $ci->tenant->id))
				->update('rest_access',
						 array('locked' => 1));

		}

		return $user_id;
	}

}