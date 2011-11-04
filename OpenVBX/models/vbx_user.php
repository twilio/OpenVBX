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

	static public $select = array(
							'users.*',
							'at.description as auth_type'
						);

	public $fields =  array(
						'id',
						'is_admin', 
						'is_active', 
						'first_name',
						'last_name', 
						'password', 
						'invite_code',
						'email', 
						'pin', 
						'notification',
						'auth_type', 
						'voicemail', 
						'tenant_id',
						'online'
					);

	public $admin_fields = array('');
	
	public $devices;

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
		
		$user = self::search($search_options, 1, 0);

		return $user;
	}

	static function search($search_options = array(), $limit = -1, $offset = 0)
	{
		$ci =& get_instance();
		
		$sql_options = array(
			'joins' => self::$joins,
			'select' => self::$select,
		);
		$user = new VBX_User();
		$users = parent::search(
			self::$__CLASS__,
			$user->table,
			$search_options,
			$sql_options,
			$limit,
			$offset
		);

		if(empty($users))
		{
			return $users;
		}

		if($limit == 1)
		{
			$users = array($users);
		}

		$ci->load->model('vbx_device');
		foreach($users as $i => $user)
		{
			$users[$i]->devices = VBX_Device::search(array('user_id' => $user->id), 100);

			if ($users[$i]->setting('online') && $users[$i]->setting('online') != 9) 
			{
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
		}
		
		if($limit == 1 && count($users) == 1)
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
			{
				return FALSE;
			}
			
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
				try
				{
					$user->setting_set('last_login', new MY_ModelLiteral('UTC_TIMESTAMP()'));
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
		if ($user->password != self::salt_encrypt($password)) 
		{
			return FALSE;
		} 
		else 
		{
			// Login succeeded
			if(OpenVBX::schemaVersion() >= 24)
			{
				try
				{
					$user->setting_set('last_login', new MY_ModelLiteral('UTC_TIMESTAMP()'));
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
		if($password != $confirmed_password) 
		{
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

	/**
	 * @deprecated use VBX_User::search() instead
	 */
	function get_users($user_ids)
	{
		_deprecated_notice(__METHOD__, '1.1.2', 'VBX_User::search()');
		
		if (!is_array($user_ids))
		{
			$user_ids = array($user_ids);
		}
		
		$search_opts = array(
			'id__in' => $user_ids
		);
		
		return self::search($search_opts);
	}

	/**
	 * @deprecated use VBX_User::get() instead
	 * @param string $user_id 
	 * @return mixed object/null
	 */
	function get_user($user_id)
	{
		_deprecated_notice(__METHOD__, '1.1.2', 'VBX_User::get');
		return self::get($user_id);
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

	/**
	 * @deprecated 1.1.x
	 * @return void
	 */
	public function get_active_users()
	{
		_deprecated_notice(__METHOD__, '1.1.2', 'VBX_User::search');
		return self::search(array('is_active' => 1));
	}

	public function send_reset_notification()
	{
		/* Set a random invitation code for resetting password */
		$this->invite_code = substr(self::salt_encrypt(mt_rand()), 0, 20);
		$this->save();

		/* Email the user the reset url */
		$maildata = array(
			'invite_code' => $this->invite_code,
			'reset_url' => tenant_url("/auth/reset/{$this->invite_code}", $this->tenant_id)
		);
		openvbx_mail($this->email, 'Reset your password', 'password-reset', $maildata);
	}

	public function send_new_user_notification()
	{
		/* Set a random invitation code for resetting password */
		$this->invite_code = substr(self::salt_encrypt(mt_rand()), 0, 20);
		$this->save();

		/* Email the user the reset url */
		$maildata = array(
			'invite_code' => $this->invite_code,
			'name' => $this->first_name,
			'reset_url' => tenant_url("/auth/reset/{$this->invite_code}", $this->tenant_id)
		);
		openvbx_mail($this->email, 'Welcome aboard', 'welcome-user', $maildata);
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
		$ci->db->from('auth_types');

		if(is_string($auth_type)) 
		{
			$ci->db->where('description', $auth_type);
		} 
		else if(is_integer($auth_type)) 
		{
			$ci->db->where('id', $auth_type);
		}

		$auth_types = $ci->db
						->get()
						->result();
			
		if(isset($auth_types[0]))
		{
			return $auth_types[0];
		}
		
		return null;
	}

	public function update()
	{		
		if (isset($this->last_seen))
		{
			$replacement = "VBX_User::setting('last_seen', new MY_ModelLiteral('UTC_TIMESTAMP()'))";
			_deprecated_notice(__CLASS__.'::$last_seen', '1.1.2', $replacement);
		}
		return parent::update();
	}

	public function save()
	{
		if (isset($this->last_seen))
		{
			$replacement = "VBX_User::setting('last_seen', new MY_ModelLiteral('UTC_TIMESTAMP()'))";
			_deprecated_notice(__CLASS__.'::$last_seen', '1.1.2', $replacement);
		}
		
		if(strlen($this->email) < 0)
		{
			throw new VBX_UserException('Email is a required field.');
		}
		
		if(!(strpos($this->email, '@') > 0))
		{
			throw new VBX_UserException('Valid email address is required');
		}
		
		if(!strlen($this->voicemail))
		{
			$this->voicemail = '';
		}
		
		$ci =& get_instance();

		if(is_string($this->auth_type) && !is_numeric($this->auth_type))
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
		{
			return null;
		}
		
		$list = implode(',', array(
			$user->id,
			$user->password,
			$user->tenant_id,
			$user->is_admin,
		));

		return self::salt_encrypt( $list );
	}
	
	/**
	 * Load the user's settings
	 * Looks to internal cached set first
	 *
	 * @return array
	 */
	public function settings()
	{	
		if (empty($this->settings))
		{
			$ci =& get_instance();
			$ci->load->model('vbx_user_setting');
			$settings = VBX_User_Setting::get_by_user($this->id);
		
			$this->settings = array();
			foreach ($settings as $setting)
			{
				$this->settings[$setting->key] = $setting;
			}
		}
		
		return $this->settings;
	}
	
	/**
	 * Get a single user setting
	 *
	 * @param string $key 
	 * @param string $default_value 
	 * @return mixed
	 */
	public function setting($key, $default_value = '')
	{
		$settings = $this->settings();
		
		if (!empty($settings[$key]))
		{
			return $settings[$key]->value;
		}
		
		return $default_value;
	}
	
	/**
	 * Save a user setting
	 * Will create the setting if it doesn't exist
	 *
	 * @param string $key 
	 * @param string $value 
	 * @return void
	 */
	public function setting_set($key, $value)
	{
		if (!is_scalar($value) && !($value instanceof My_ModelLiteral))
		{
			$value = serialize($value);
		}
		
		$settings = $this->settings();
		if (empty($settings[$key]))
		{
			$data = (object) array(
				'user_id' => $this->id,
				'key' => $key,
				'value' => $value
			);
			$settings[$key] = new VBX_User_Setting($data);
		}
		else
		{
			$settings[$key]->value = $value;
		}
		
		$settings[$key]->save();
	}
}
