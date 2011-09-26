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

class VBX_UserException extends Exception {}
class VBX_User extends MY_Model {

	protected static $__CLASS__ = __CLASS__;
	public $table = 'users';

	static public $joins = array(
								 'auth_types at' => 'at.id = users.auth_type',
								 );

	static public $select = array('users.*',
								  'at.description as auth_type');

	public $fields =  array('id','is_admin', 'is_active', 'first_name',
							'last_name', 'password', 'invite_code',
							'email', 'pin', 'notification',
							'auth_type', 'voicemail', 'tenant_id',
							'last_login', 'last_seen', 'online',
							'data');

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

		return self::search($search_options,
							1,
							0);
	}

	static function search($search_options = array(), $limit = -1, $offset = 0)
	{
		$sql_options = array('joins' => self::$joins,
							 'select' => self::$select,
							 );
		$user = new VBX_User();
		$users = parent::search(self::$__CLASS__,
								$user->table,
								$search_options,
								$sql_options,
								$limit,
								$offset);

		if(empty($users))
		{
			return $users;
		}

		if($limit == 1)
		{
			$users = array($users);
		}

		$ci = &get_instance();
		$ci->load->model('vbx_device');
		foreach($users as $i => $user)
		{
			$users[$i]->devices = VBX_Device::search(array('user_id' => $user->id), 100);
		}

		// check if the users online
		$userOnline = $users[$i]->online;
		$maxOfflineTime = 1 * 60; // 1 minute offline time
		if ($userOnline) {
			// Check last seen
			$last = new DateTime($users[$i]->last_seen);
			$now = new DateTime();
			if ($now->format('U') - $last->format('U') > $maxOfflineTime) {
				$userOnline = false;
			}
		}

		if ($userOnline) {
			array_unshift($users[$i]->devices, new VBX_Device((object) array(
												'id' => 0,
												'name' => 'client',
												'value' => 'client:'.$users[$i]->id,
												'sms' => 0,
												'sequence' => -99,
												'is_active' => 1,
												'user_id' => $users[$i]->id
											)));
		}

		/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
		// Remove phono addresses from array
		foreach ($users[$i]->devices as $j => $device) {
			if ($device->name == 'Phono') {
				unset($users[$i]->devices[$j]);
			}
		}
		/** End Disruptive Technologies code **/

		if($limit == 1
		   && count($users) == 1)
		{
			return $users[0];
		}

		return $users;
	}

	static function authenticate($email, $password, $captcha, $captcha_token)
	{
		$user = VBX_User::get(array('email' => $email));
		if (empty($user))
		{
			return FALSE;
		}
		else
		{
			/* Check if active */
			if(!$user->is_active)
				return FALSE;

			switch($user->auth_type)
			{
				case 'google':
					return self::login_google($user, $email, $password, $captcha, $captcha_token);
				case 'openvbx':
				default:
					return self::login_openvbx($user, $password);
			}
		}
	}

	function login_google($user, $email, $password, $captcha, $captcha_token)
	{
		$this->load->library('GoogleDomain');
		try
		{
			$auth_response = GoogleDomain::authenticate($email, $password,
														$captcha, $captcha_token);

			if(OpenVBX::schemaVersion() >= 24)
			{
				// Login succeeded
				$user->last_login = new MY_ModelLiteral('UTC_TIMESTAMP()');
				try
				{
					$user->save();
				}
				catch(VBX_UserException $e)
				{
					$this->error_message('login', $e->getMessage());
					return FALSE;
				}
			}
		}
		catch(GoogleDomainException $e)
		{
			$this->error_message('login', $e->getMessage());
			return FALSE;
		}

		return $user;
	}

	function login_openvbx($user, $password)
	{
		if ($user->password != self::salt_encrypt($password)) {
			return FALSE;
		} else {
			// Login succeeded
			if(OpenVBX::schemaVersion() >= 24)
			{
				$user->last_login = new MY_ModelLiteral('UTC_TIMESTAMP()');
				try
				{
					$user->save();
				}
				catch(VBX_UserException $e)
				{
					$this->error_message('login', $e->getMessage());
					return FALSE;
				}
			}
			return $user;
		}
	}

	function full_name()
	{
		$full_name = trim($this->first_name . ' ' . $this->last_name);
		return empty($full_name) ? $this->email : $full_name;
	}

	function set_password($password, $confirmed_password)
	{
		if($password != $confirmed_password) {
			throw(new VBX_UserException("Password typed incorrectly"));
		}
		$ci =& get_instance();
		$ci->load->helper('email');
		$this->password = self::salt_encrypt($password);
		$this->invite_code = self::salt_encrypt($password);
		try
		{
			$result = $this->save();
		}
		catch(Exception $e)
		{
			error_log($e->getMessage());
			return false;
		}

		return $result;
	}

	// return an array of all the ids of the groups this user belongs to
	static function get_group_ids($user_id)
	{
		$ci = &get_instance();
		$result = $ci->db
			->from('groups_users')
			->where('user_id', $user_id)
			->get()->result();

		$group_ids = array();
		if(!empty($result))
		{
			foreach($result as $group_user)
			{
				$group_ids[] = $group_user->group_id;
			}
		}

		return $group_ids;
	}

	function get_users($user_ids)
	{
		if(empty($user_ids))
			return array();

		$this->where_in('id', $user_ids);

		return $this->get();
	}

	function get_user($user_id)
	{
		$ci = &get_instance();
		$ci->db
			->from($this->table . ' as u')
			->where('id', intval($user_id));

		$users = $ci->db->get()->result();

		if(!empty($users))
			return $users[0];

		return NULL;
	}

	/**
	 * Encrypt (prep)
	 *
	 * Encrypts this objects password with a random salt.
	 *
	 * @access	private
	 * @param	string
	 * @return	void
	 */
	public function _encrypt($field)
	{
		if (!empty($this->$field))
		{
			$this->$field = self::salt_encrypt($this->$field);
		}
	}

	public function get_active_users()
	{
		$ci =& get_instance();

		$ci->db->flush_cache();
		$result = $ci->db
			->select('users.*,'.
					 'at.description as auth_type')
			->join('auth_types at', 'at.id = users.auth_type')
			->where('is_active', 1)
			->where('users.tenant_id', $ci->tenant->id)
			->from($this->table)
			->get()->result();

		return $result;
	}

	public function send_reset_notification()
	{

		/* Set a random invitation code for resetting password */
		$this->invite_code = substr(self::salt_encrypt(mt_rand()), 0, 20);
		$this->save();

		/* Email the user the reset url */
		$maildata = array('invite_code' => $this->invite_code,
						  'reset_url' => tenant_url("/auth/reset/{$this->invite_code}", $this->tenant_id));
		openvbx_mail($this->email,
					 'Reset your password',
					 'password-reset',
					 $maildata);
	}

	public function send_new_user_notification()
	{
		/* Set a random invitation code for resetting password */
		$this->invite_code = substr(self::salt_encrypt(mt_rand()), 0, 20);
		$this->save();

		/* Email the user the reset url */
		$maildata = array('invite_code' => $this->invite_code,
						  'name' => $this->first_name,
						  'reset_url' => tenant_url("/auth/reset/{$this->invite_code}", $this->tenant_id));
		openvbx_mail($this->email,
					 'Welcome aboard',
					 'welcome-user',
					 $maildata);
	}


	public static function salt_encrypt($value)
	{
		$salt = config_item('salt');
		$result = sha1($salt . $value);
		return $result;
	}

	function get_auth_type($auth_type = null)
	{
		$ci = &get_instance();
		$ci->db
			 ->from('auth_types');

		if(is_string($auth_type)) {
			$ci->db
				->where('description', $auth_type);
		} else if(is_integer($auth_type)) {
			$ci->db
				->where('id', $auth_type);
		}

		$auth_types = $ci->db
			 ->get()->result();
		if(isset($auth_types[0]))
			return $auth_types[0];

		return null;
	}

	public function save()
	{
		if(strlen($this->email) < 0)
			throw new VBX_UserException('Email is a required field.');

		if(!(strpos($this->email, '@') > 0))
			throw new VBX_UserException('Valid email address is required');

		if(!strlen($this->voicemail))
			$this->voicemail = '';

		$ci =& get_instance();

		if(is_string($this->auth_type))
		{
			$results = $ci->db
				->from('auth_types')
				->where('description', $this->auth_type)
				->get()->result();

			if(empty($results))
			{
				throw new VBX_UserException('AuthType does not exist.');
			}

			$this->auth_type = $results[0]->id;
		}

		return parent::save();
	}

	public static function signature($user_id)
	{
		$user = VBX_User::get($user_id);
		if(!$user)
			return null;

		$list = implode(',', array(
								   $user->id,
								   $user->password,
								   $user->tenant_id,
								   $user->is_admin,
								   ));

		return self::salt_encrypt( $list );
	}
}
