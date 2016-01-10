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

/**
 * Class VBX_User
 * @property int $id
 * @property int $is_admin
 * @property int $is_active
 * @property string $first_name
 * @property string $last_name
 * @property string $password
 * @property string $invite_code
 * @property string $email
 * @property string $pin
 * @property string $notification
 * @property string $auth_type
 * @property string $voicemail
 * @property int $tenant_id
 */
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

	public $fields = array(
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
					);

	public $admin_fields = array('is_admin');
	
	public $devices;
	
	/**
	 * Set the default min-password length
	 * Fortunately the word "password" is 8 characters ;)
	 *
	 * @var int
	 */
	const MIN_PASSWORD_LENGTH = 8;

	const HASH_ITERATION_COUNT = 8;
	const PORTABLE_HASHES = FALSE;

	protected $settings;
	public $settings_available;

	public function __construct($object = null)
	{
		/**
		 * User settings can be problematic during upgrade from versions
		 * that didn't have settings. We need to neuter the settings in
		 * these cases to allow the upgrade to process before worrying
		 * about using the settings database table
		 */
		$this->settings_available = version_compare(OpenVBX::schemaVersion(), '61', '>=');
		parent::__construct($object);
	}

	/**
	 * @param array $search_options
	 * @param int $limit
	 * @param int $offset
	 * @return array|bool|null|VBX_User
	 */
	public static function get($search_options = array(), $limit = -1, $offset = 0)
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

	/**
	 * @param array $search_options
	 * @param int $limit
	 * @param int $offset
	 * @return VBX_User[]|VBX_User
	 * @throws MY_ModelException
	 */
	public static function search($search_options = array(), $limit = -1, $offset = 0)
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

		/** @var VBX_User[] $users **/
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

	/**
	 * @param string $email
	 * @param string $password
	 * @param string $captcha
	 * @param string $captcha_token
	 * @return bool|mixed
	 */
	public static function login($email, $password, $captcha, $captcha_token)
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

	/**
	 * @param VBX_User $user
	 * @param string $email
	 * @param string $password
	 * @param string $captcha
	 * @param string $captcha_token
	 * @return bool
	 * @throws GoogleCaptchaChallengeException
	 */
	protected function login_google($user, $email, $password, $captcha, $captcha_token)
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

	/**
	 * Attempt to log in the user
	 *
	 * @param VBX_User $user 
	 * @param string $password 
	 * @return mixed VBX_User on success, bool FALSE on failure
	 */
	protected function login_openvbx($user, $password)
	{				
		if (!self::authenticate($user, $password)) 
		{
			return FALSE;
		} 
		else 
		{
			// Login succeeded
			if(OpenVBX::schemaVersion() >= 24)
			{
				$user->last_login = new MY_ModelLiteral('UTC_TIMESTAMP()');
				// auto-upgrade old passwords
				if (OpenVBX::schemaVersion() > 63 && strlen($user->password) == 40)
				{
					$user->password = self::salt_encrypt($password);
					$user->save();
				}

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

	/**
	 * Authenticate a user
	 * simple true/false return on password check
	 * 
	 * Will attempt legacy login if 1st attempt fails
	 *
	 * @param VBX_User $user 
	 * @param string $password 
	 * @return bool
	 */
	public static function authenticate($user, $password)
	{		
		// if we're not passed a user object 
		// assume that it is a user_id
		if (!($user instanceof VBX_User))
		{
			$user = VBX_User::get(intval($user));
		}
		
		if (!class_exists('PasswordHash'))
		{
			require_once(APPPATH.'libraries/PasswordHash.php');
		}
		
		$hashr = new PasswordHash(self::HASH_ITERATION_COUNT, self::PORTABLE_HASHES);
		$login = $hashr->CheckPassword(self::salt_password($password), $user->password);
				
		// attempt legacy login on failure
		if (!$login && strlen($user->password) == 40)
		{
			$login = ($user->password == self::salt_encrypt($password, true));
		}
				
		return $login;
	}

	/**
	 * @param $user
	 * @param $signature
	 * @return bool
	 */
	public static function check_signature($user, $signature)
	{	
		return ($signature == self::signature($user));
	}

	/**
	 * Return the user's full name
	 *
	 * @return string
	 */
	public function full_name()
	{
		$full_name = trim($this->first_name . ' ' . $this->last_name);
		return empty($full_name) ? $this->email : $full_name;
	}

	/**
	 * Set the user's password
	 * 
	 * Validation rules:
	 * - the password & confirmation must match
	 * - password cannot be empty
	 * - password must be self::MIN_PASSWORD_LENGTH to be valid
	 *
	 * @throws VBX_UserException
	 * @param string $password 
	 * @param string $confirmed_password 
	 * @return bool
	 */
	public function set_password($password, $confirmed_password)
	{
		$password = trim($password);
		$confirmed_password = trim($confirmed_password);
		
		if ($password != $confirmed_password) 
		{
			throw new VBX_UserException("Password typed incorrectly");
		}
		
		if (strlen($password) == 0)
		{
			throw new VBX_UserException('Password cannot be empty');
		}
		elseif (strlen($password) < self::MIN_PASSWORD_LENGTH)
		{
			throw new VBX_UserException('Password must be at least '.self::MIN_PASSWORD_LENGTH.
										' characters in length');
		}
		
		$ci =& get_instance();
		$ci->load->helper('email');
		
		$this->password = self::salt_encrypt($password);
		$this->invite_code = $this->generate_invite_code();
		
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

	/**
	 * return an array of all the ids of the 
	 * groups this user belongs to
	 *
	 * @param int $user_id
	 * @return array
	 */
	public static function get_group_ids($user_id)
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
	 * @param array $user_ids
	 * @return mixed
	 */
	static function get_users($user_ids)
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
	 * @deprecated 1.2 - doesn't appear to be used anywhere
	 * @param	string
	 * @return	void
	 */
	protected function _encrypt($field)
	{
		_deprecated_notice(__METHOD__, '1.2');
		if (!empty($this->$field))
		{
			$this->$field = self::salt_encrypt($this->$field);
		}
	}

	/**
	 * @deprecated 1.1.x
	 * @return VBX_User|VBX_User[]
	 */
	public function get_active_users()
	{
		_deprecated_notice(__METHOD__, '1.1.2', 'VBX_User::search');
		return self::search(array('is_active' => 1));
	}

    /*
     * This method attempts to send the notification and gives us a true/false
     *    depending on if it worked.
     *
     * @return boolean
     */
	public function send_reset_notification()
	{
		// Set a random invitation code for resetting password
		$this->invite_code = $this->generate_invite_code();
		$this->save();

		// Email the user the reset url
		$maildata = array(
			'invite_code' => $this->invite_code,
			'reset_url' => tenant_url("/auth/reset/{$this->invite_code}", $this->tenant_id)
		);
		return openvbx_mail($this->email, 'Reset your password', 'password-reset', $maildata);
	}

	/*
	 * This method attempts to send the email and returns a boolean depending on if it worked.
	 *
	 * @return boolean
	 */
	public function send_new_user_notification()
	{
		// Set a random invitation code for resetting password
		$this->invite_code = $this->generate_invite_code();
		$this->save();

		// Email the user the reset url
		$maildata = array(
			'invite_code' => $this->invite_code,
			'name' => $this->first_name,
			'reset_url' => tenant_url("/auth/reset/{$this->invite_code}", $this->tenant_id)
		);
		return openvbx_mail($this->email, 'Welcome aboard', 'welcome-user', $maildata);
	}

	/**
	 * Encrypt a string
	 * For legacy it uses the 'salt' value from `OpenVBX/config/openvbx.php`
	 * 
	 * @param string $value 
	 * @param bool $legacy - wether to user the older sha1 method
	 * @return string
	 */
	public static function salt_encrypt($value, $legacy = false)
	{
		$salt = config_item('salt');
		
		if ($legacy === false)
		{
			if (!class_exists('PasswordHash'))
			{
				require_once(APPPATH.'libraries/PasswordHash.php');
			}

			$hashr = new PasswordHash(self::HASH_ITERATION_COUNT, self::PORTABLE_HASHES);
			$result = $hashr->HashPassword(self::salt_password($value));
		}
		else
		{
			$result = sha1($salt.$value);
		}
		
		return $result;
	}

	/**
	 * @param string $password
	 * @return string
	 */
	protected static function salt_password($password)
	{
		$salt = config_item('salt');
		return md5($salt.$password);
	}

	/**
	 * @return string
	 */
	protected function generate_invite_code()
	{
		return substr(base64_encode(self::salt_encrypt(mt_rand())), 0, 20);
	}

	/**
	 * @param null|int|string $auth_type
	 * @return null
	 */
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

		$auth_types = $ci->db->get()->result();

		if(isset($auth_types[0]))
		{
			return $auth_types[0];
		}
		
		return null;
	}

	/**
	 * @param $id
	 * @param $params
	 * @return mixed
	 */
	public function update($id, $params)
	{		
		if (isset($params->last_seen))
		{
			$replacement = "VBX_User::setting('last_seen', new MY_ModelLiteral('UTC_TIMESTAMP()'))";
			_deprecated_notice(__CLASS__.'::$last_seen', '1.1.2', $replacement);
		}
		return parent::update($id, $params);
	}

	/**
	 * @param bool|false $force_update
	 * @return bool
	 * @throws VBX_UserException
	 */
	public function save($force_update = false)
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

		return parent::save($force_update);
	}

	/**
	 * @param $user
	 * @return null|string
	 */
	public static function signature($user)
	{
		if (is_numeric($user))
		{
			$user = VBX_User::get($user);
		}
				
		if(!$user || !is_object($user))
		{
			return null;
		}
		
		$list = implode(',', array(
		   $user->id,
		   $user->password,
		   $user->tenant_id,
		   $user->is_admin
		));

		return self::salt_encrypt($list, true);
	}
	
	/**
	 * Load the user's settings
	 * Looks to internal cached set first
	 *
	 * @return array
	 */
	public function settings()
	{	
		if (empty($this->settings) && $this->settings_available)
		{
			$ci =& get_instance();
			$ci->load->model('vbx_user_setting');
			$settings = VBX_User_Setting::get_by_user($this->id);
		
			$this->settings = array();
			if (!empty($settings))
			{
				foreach ($settings as $setting)
				{
					$this->settings[$setting->key] = $setting;
				}
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
	 * @return bool
	 */
	public function setting_set($key, $value)
	{
		if (!$this->settings_available)
		{
			return false;
		}
		
		if (!is_scalar($value) && !($value instanceof My_ModelLiteral))
		{
			$value = serialize($value);
		}

		/** @var VBX_User_Setting[] $settings */
		$settings = $this->settings();
		if (empty($settings[$key]))
		{
			$data = (object) array(
				'user_id' => $this->id,
				'key' => $key,
				'value' => $value,
				'tenant_id' => $this->tenant_id
			);
			$settings[$key] = new VBX_User_Setting($data);
		}
		else
		{
			$settings[$key]->value = $value;
		}

		return $settings[$key]->save();
	}
}
