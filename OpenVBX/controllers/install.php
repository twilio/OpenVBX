<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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

class InstallException extends Exception {}

/**
 * Class Install
 * @property CI_Loader $load
 * @property CI_Config $config
 * @property CI_Input $input
 */
class Install extends Controller {

	public $tenant;

	public $tests;
	public $pass;

	protected $user = array();
	protected $database = array();
	protected $openvbx_settings = array();
	protected $openvbx = array();

	private $account;
	protected $min_php_version = MIN_PHP_VERSION;

	public $cache;

	public function __construct()
	{
		parent::Controller();
		if(file_exists(APPPATH . 'config/openvbx.php'))
		{
			$this->config->load('openvbx');
		}

		if(file_exists(APPPATH . 'config/database.php')
			AND version_compare(PHP_VERSION, $this->min_php_version, '>='))
		{
			$this->load->database();
			redirect('');
		}

		// cache is evil when not handled properly, assume the
		// possibility that we're being reinstalled so lets clear
		// any possibly lingering cache artifacts
		$this->cache = OpenVBX_Cache_Abstract::load();
		$this->cache->enabled(true);
		$this->cache->flush();
		$this->cache->enabled(false);
	}

	private function input_args()
	{
		$tplvars = array();
		$tplvars['pass'] = true;

		$this->database['username'] = trim($this->input->post('database_user'));
		$this->database['password'] = $this->input->post('database_password');
		$this->database['hostname'] = trim($this->input->post('database_host') == ""?
										   'localhost'
										   : $this->input->post('database_host'));
		$this->database['database'] = trim($this->input->post('database_name') == ""?
										   'OpenVBX'
										   : $this->input->post('database_name'));

		$this->openvbx_settings = array();
		$this->openvbx = array();

		$this->openvbx_settings['twilio_sid'] = trim($this->input->post('twilio_sid'));
		$this->openvbx_settings['twilio_token'] = trim($this->input->post('twilio_token'));
		$this->openvbx_settings['connect_application_sid'] = trim($this->input->post('connect_application_sid'));

		$this->openvbx['salt'] = md5(rand(10000, 99999));
		$this->openvbx_settings['from_email'] = trim($this->input->post('from_email') == ""?
													 ''
													 : $this->input->post('from_email'));
		$this->openvbx_settings['theme'] = $this->input->post('theme');
		$this->openvbx_settings['iphone_theme'] = '';
		$this->openvbx_settings['trial_number'] = '(415) 599-2671';
		$this->openvbx_settings['schema-version'] = OpenVBX::getLatestSchemaVersion();

		$this->openvbx_settings['rewrite_enabled'] = !strlen($this->input->post('rewrite_enabled'))? 0 : $this->input->post('rewrite_enabled');
		$this->openvbx_settings['application_sid'] = '';

		$this->user = array();
		$this->user['email'] = trim($this->input->post('admin_email'));
		$this->user['password'] = $this->input->post('admin_pw');
		$this->user['firstname'] = trim($this->input->post('admin_firstname'));
		$this->user['lastname'] = trim($this->input->post('admin_lastname'));
		$this->user['tenant_id'] = 1;

		$tplvars = array_merge($tplvars, $this->user, $this->database, $this->openvbx, $this->openvbx_settings);

		return $tplvars;
	}

	private function run_tests()
	{
		$this->tests = array();
		$this->pass = TRUE;

		$this->pre_test_htaccess();

		$this->add_test(version_compare(PHP_VERSION, $this->min_php_version, '>='),
						'PHP Version',
						'Supported: '.PHP_VERSION,
						'You must be running at least PHP '.$this->min_php_version.'; you are using ' . PHP_VERSION);

		$this->add_test(function_exists('mysql_connect'),
						'MySQL',
						'supported',
						'missing');

		$this->add_test(function_exists('simplexml_load_file'),
						'SimpleXML',
						'supported',
						'missing');

		$this->add_test(extension_loaded("curl"),
						'CURL',
						'supported',
						'missing');

		$this->add_test(extension_loaded("apc"),
						'APC',
						'supported',
						'missing, but optional',
						false);

		$this->add_test(extension_loaded('memcache'),
						'Memcache',
						'supported',
						'missing, but optional',
						false);

		$this->add_test(function_exists('json_encode'),
						'JSON',
						'supported',
						'missing');

		$apache_version = function_exists('apache_get_version')? apache_get_version() : '';
		$this->add_test(function_exists('apache_request_headers'),
						'Apache Version',
						$apache_version,
						'missing, but optional',
						false);

		$this->add_test(is_writable(APPPATH.'config'),
						'Config Dir',
						'writable',
						'permission denied: '.APPPATH.'config');

		$this->add_test(is_writable(APPPATH.'../audio-uploads'),
						'Upload Dir',
						'writable',
						'permission denied: '.realpath(APPPATH.'../audio-uploads'));

		$this->add_test(is_file(APPPATH.'../.htaccess'),
						'.htaccess File',
						'found',
						'missing, HIGHLY recommended',
						false);
	}

	public function index()
	{
		// perform install tests
		$tplvars = $this->input_args();
		$this->run_tests();

		$tplvars['tests'] = $this->tests;
		$tplvars['pass'] = $this->pass;

		$this->load->view('install/main', $tplvars);
	}

	private function add_test($pass, $name, $pass_text, $fail_text, $required = true)
	{
		$pass = (boolean)$pass;
		$this->tests[] = array(
			'name' => $name,
			'pass' => $pass,
			'required' => $required,
			'message' => ($pass ? $pass_text : $fail_text)
		);

		if($required)
		{
			$this->pass = $this->pass && $pass;
		}
	}

	private function get_database_params($database)
	{
		$database = $this->database;

		$database['dbdriver'] = 'mysql';
		$database['dbprefix'] = '';
		$database['pconnect'] = FALSE;
		$database['db_debug'] = FALSE;
		$database["cache_on"] = FALSE;
		$database["cachedir"] = "";
		$database["char_set"] = "utf8";
		$database["dbcollat"] = "utf8_general_ci";

		return array(
			'global' => array(
				'active_group' => "default",
				'active_record' => TRUE,
			),
			'default' => $database
		);
	}

	public function setup()
	{
		$tplvars = $this->input_args();

		$this->run_tests();

		$json['tests'] = $this->tests;
		$json['pass'] = $this->pass;
		$json['success'] = true;
		$json['step'] = 6;

		$database = $this->get_database_params($this->database);
		$openvbx = $this->openvbx;
		$openvbx_settings = $this->openvbx_settings;
		$user = $this->user;

		try
		{
			$openvbx_settings['application_sid'] = $this->get_application($this->openvbx_settings);

			if(!($dbh = @mysql_connect($database['default']['hostname'],
									   $database['default']['username'],
									   $database['default']['password'])))
			{
				throw new InstallException( "Failed to connect to database: "
											. mysql_error(), 2 );
			}

			// test for mysqli compat
			if (function_exists('mysqli_connect'))
			{
				// server info won't work without first selecting a table
				mysql_select_db($database['default']['database']);
				$server_version = mysql_get_server_info($dbh);
				if (!empty($server_version)) {
					if (version_compare($server_version, '4.1.13', '>=')
						&& version_compare($server_version, '5', '<'))
					{
						$database['default']['dbdriver'] = 'mysqli';
					}
					elseif (version_compare($server_version, '5.0.7', '>='))
					{
						$database['default']['dbdriver'] = 'mysqli';
					}
				}
			}

			$this->setup_database($database, $dbh);
			$this->setup_config($database, $openvbx);
			$this->setup_openvbx_settings($openvbx_settings);
			$this->setup_user($user);

			if (!empty($openvbx_settings['connect_application_sid']))
			{
				$this->setup_connect_app($openvbx_settings);
			}
		}
		catch(InstallException $e)
		{
			/* Clean up our dirty work */
			@unlink(APPPATH. 'config/database.php');
			@unlink(APPPATH. 'config/openvbx.php');

			$json['success'] = false;
			$json['error'] = $e->getMessage();
			$json['step'] = $e->getCode();
		}

        $this->json_return($json);
	}

	private function setup_connect_app($settings)
	{
		try {
			$account = OpenVBX::getAccount($settings['twilio_sid'], $settings['twilio_token']);
			$connect_application = $account->connect_apps->get($settings['connect_application_sid']);

			if ($connect_application->sid == $settings['connect_application_sid'])
			{
				$site_url = site_url();
				if ($settings['rewrite_enabled'])
				{
					$site_url = str_replace('/index.php', '', $site_url);
				}

				$required_settings = array(
					'HomepageUrl' => $site_url,
					'AuthorizeRedirectUrl' => $site_url.'/auth/connect',
					'DeauthorizeCallbackUrl' => $site_url.'/auth/connect/deauthorize',
					'Permissions' => array(
						'get-all',
						'post-all'
					)
				);

				$updated = false;
				foreach ($required_settings as $key => $setting)
				{
					$app_key = Services_Twilio::decamelize($key);
					if ($connect_application->$app_key != $setting)
					{
						$connect_application->$app_key = $setting;
						$updated = true;
					}
				}

				if ($updated)
				{
					$connect_application->update(array(
						'FriendlyName' => $connect_application->friendly_name,
						'Description' => $connect_application->description,
						'CompanyName' => $connect_application->company_name,
						'HomepageUrl' => $required_settings['HomepageUrl'],
						'AuthorizeRedirectUrl' => $required_settings['AuthorizeRedirectUrl'],
						'DeauthorizeCallbackUrl' => $required_settings['DeauthorizeCallbackUrl'],
						'Permissions' => implode(',', $required_settings['Permissions'])
					));
				}
			}
		}
		catch (Exception $e) {
			throw new InstallException($e->getMessage(), $e->getCode());
		}
	}

	private function setup_database($database, $dbh)
	{
		if(!mysql_select_db($database['default']['database'], $dbh))
		{
			throw new InstallException( "Failed to access database: ". mysql_error($dbh), 2);
		}

		$sql_file = file_get_contents(APPPATH . '../openvbx.sql');
		$sql_lines = explode(';', $sql_file);
		foreach($sql_lines as $sql)
		{
			$sql = trim($sql);
			if(empty($sql))
			{
				continue;
			}

			if(!mysql_query($sql, $dbh))
			{
				throw new InstallException( "Failed to run sql: ".$sql. " :: ".
											mysql_error($dbh), 2);
			}
		}

	}

	private function setup_config($database, $openvbx)
	{
		$this->write_config(APPPATH. 'config/database.php', $database, 'db');
		$this->write_config(APPPATH. 'config/openvbx.php', $openvbx, 'config');

		if(!is_file(APPPATH. 'config/database.php') || !is_file(APPPATH. 'config/openvbx.php'))
		{
			throw new InstallException('Failed to write configuration files', 1);
		}

		return;
	}

	private function write_config($filename, $fields = array(), $group)
	{
		$fp = fopen($filename, 'w+');
		if(!$fp)
		{
			throw new InstallException( 'Failed to write database configuration file', 1 );
		}

		$config = "<?php\n";
		foreach($fields as $field_group => $field_set)
		{
			if(!is_array($field_set))
			{
				$value = $field_set;
				$key = $field_group;

				if(is_bool($value))
				{
					$config .= '$'.$group."['$key'] = ".($value? 'TRUE' : 'FALSE').";\n";
				}
				else
				{
					$config .= '$'.$group."['$key'] = '$value';\n";
				}
				continue;
			}

			foreach($field_set as $key => $value)
			{
				if(is_bool($value))
				{
					if($field_group == 'global')
					{
						$config .= '$'.$key." = ".($value? 'TRUE' : 'FALSE').";\n";
					}
					else
					{
						$config .= '$'.$group."['$field_group']['$key'] = ".($value? 'TRUE' : 'FALSE').";\n";
					}
				}
				else
				{
					if($field_group == 'global')
					{
						$config .= '$'.$key." = '$value';\n";
					}
					else
					{
						$config .= '$'.$group."['$field_group']['$key'] = '$value';\n";
					}
				}
			}
		}
		$config .= "/* Autogenerated Configuration for $group ".date('Y-m-d'). " */\n";
		if(!fwrite($fp, $config))
		{
			throw new InstallException('Failed to write config', 1);
		}
		fclose($fp);
	}

	private function setup_user($user)
	{
		$this->load->database();
		$this->config->load('openvbx');
		$this->load->model('vbx_user');
		$admin = new VBX_User();
		$admin->email = $user['email'];
		$admin->password = VBX_User::salt_encrypt($user['password']);
		$admin->first_name = $user['firstname'];
		$admin->last_name = $user['lastname'];
		$admin->tenant_id = $user['tenant_id'];
		$admin->is_admin = true;
		$admin->voicemail = 'Please leave a message after the beep.';

		try
		{
			$admin->save();
			$admin->setting_set('online', 9);
		}
		catch(Exception $e)
		{
			throw new InstallException( $e->getMessage(), 4 );
		}
	}

	private function setup_openvbx_settings($settings)
	{
		$this->load->database();
		$this->config->load('openvbx');
		$this->load->model('vbx_settings');

		if(isset($this->vbx_settings)) $this->settings = $this->vbx_settings;

		try
		{
			foreach($settings as $key => $val)
			{
				if($this->vbx_settings->add($key, $val, 1) === false)
				{
					throw new InstallException('Failed to create setting for '.
												$key.'. Please re-create database', 0);
				}
			}
		}
		catch(SettingsException $e)
		{
			throw new InstallException('Unable to setup valid instance. '.
										'Please re-create your database');
		}
	}

	/**
	 * Check for the existence of a Twilio Client specific application
	 * Create one if necessary
	 *
	 * @throws InstallException
	 * @param array $settings
	 * @return string Application Sid
	 */
	private function get_application($settings)
	{
		try
		{
			$app_token = md5($_SERVER['REQUEST_URI']);
			$app_name = "OpenVBX - {$app_token}";

			if (empty($this->account))
			{
				$this->account = OpenVBX::getAccount($settings['twilio_sid'], $settings['twilio_token']);
			}
			$applications = $this->account->applications->getIterator(0, 10, array(
				'FriendlyName' => $app_name
			));

			$application = false;

			/** @var Services_Twilio_Rest_Application $_application */
			foreach ($applications as $_application)
			{
				if ($_application->friendly_name == $app_name)
				{
					$application = $_application;
					break;
				}
			}

			$site_url = site_url();
			if ($settings['rewrite_enabled'])
			{
				$site_url = str_replace('/index.php', '', $site_url);
			}

			$params = array(
				'FriendlyName' => $app_name,
				'VoiceUrl' => $site_url.'/twiml/dial',
				'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
				'VoiceMethod' => 'POST',
				'SmsUrl' => '',
				'SmsFallbackUrl' => '',
				'SmsMethod' => 'POST'
			);

			if (!empty($application))
			{
				$application->update($params);
			}
			else
			{
				$application = $this->account->applications->create($app_name, $params);
			}
		}
		catch(Exception $e)
		{
			throw new InstallException($e->getMessage());
		}

		return $application->sid;
	}

	function validate()
	{
		$step = $this->input->post('step');
		$json = array(
			'success' => true
		);

		if($step == 1)
		{
            $this->json_return($json);
		}

		$tplvars = $this->input_args();
		switch($step)
		{
			case 2:
				$json = $this->validate_step2();
				break;
			case 3:
				$json = $this->validate_step3();
				break;
			case 4:
				$json = $this->validate_step4();
				break;
			case 5:
				$json = $this->validate_step5();
				break;

		}

		$json['tplvars'] = $tplvars;
        $this->json_return($json);
	}

	function validate_step2()
	{
		$json = array(
			'success' => true,
			'step' => 2,
			'message' =>
			'success'
		);

		$database = $this->get_database_params($this->database);

		try
		{
			if(!($dbh = @mysql_connect($database['default']['hostname'],
									   $database['default']['username'],
									   $database['default']['password'])))
			{
				$error = mysql_error();
				$json['errors'] = array(
					'hostname' => $error,
					'username' => '',
					'password' => ''
				);
				throw new InstallException("Failed to connect to database: $error", 2);
			}

			if(!mysql_select_db($database['default']['database'], $dbh))
			{
				$error = mysql_error($dbh);
				$json['errors'] = array('database_name' => $error );
				throw new InstallException("Failed to access database: $error", 2);
			}
		}
		catch(InstallException $e)
		{
			$json['success'] = false;
			$json['message'] = $e->getMessage();
		}

		return $json;
	}

	/**
	 * Verify the Account Sid & Token
	 * Request a list of accounts with the credentials. Exceptions will
	 * give us our error conditions. Only known right now is 20003 (auth denied)
	 *
	 * @return array
	 */
	function validate_step3()
	{
		$this->load->model('vbx_settings');

		$json = array(
			'success' => true,
			'step' => 3,
			'message' => 'success'
		);
		$twilio_sid = $this->openvbx_settings['twilio_sid'];
		$twilio_token = $this->openvbx_settings['twilio_token'];
		$connect_app = $this->openvbx_settings['connect_application_sid'];
		try
		{
			// call for most basic of information to see if we have access
			$account = OpenVBX::getAccount($twilio_sid, $twilio_token);

			/**
			 * We'll get an account back with empty members, even if we supplied
			 * bunk credentials, we need to verify that something is there to be
			 * confident of success.
			 */
			$status = $account->type;
			if (empty($status))
			{
				throw new InstallException('Unable to access Twilio Account');
			}

			// check the connect app if a sid is provided
			if (!empty($connect_app))
			{
				try {
					$connect_application = $account->connect_apps->get($connect_app);
					$friendly_name = $connect_application->friendly_name;
				}
				catch (Exception $e) {
					switch ($e->getCode())
					{
						case 0:
							// return a better message than "resource not found"
							throw new InstallException('The Connect Application SID &ldquo;'.$connect_app.
														'&rdquo; was not found.', 0);
							break;
						default:
							throw new InstallException($e->getMessage(), $e->getCode());
					}
				}
			}
		}
		catch(Exception $e)
		{
			$json['success'] = false;

			switch ($e->getCode())
			{
				case '20003':
					$json['message'] = 'Authentication Failed. Invalid Twilio SID or Token.';
					break;
				default:
					$json['message'] = $e->getMessage();
			}

			$json['message'] .= ' ('.$e->getCode().')';
		}

		return $json;
	}

	function validate_step4()
	{
		$json = array(
			'success' => true,
			'step' => 4,
			'message' => 'success'
		);
		$this->openvbx_settings['from_email'] = trim($this->input->post('from_email'));

		try
		{
			if (!filter_var($this->openvbx_settings['from_email'], FILTER_VALIDATE_EMAIL))
			{
				throw new InstallException('Email address is invalid. Please check the '.
											'address and try again.');
			}

			$required_fields = array(
				'from_email' => 'Notification Sender Email Address'
			);
			foreach($required_fields as $required_field => $label)
			{
				if(empty($this->openvbx_settings[$required_field]))
				{
					throw new InstallException('Required field: '.$label);
				}
			}
		}
		catch(InstallException $e)
		{
			$json['success'] = false;
			$json['message'] = $e->getMessage();
		}
		return $json;
	}

	function validate_step5()
	{
		$ci =& get_instance();
		$ci->load->model('vbx_user');
		$json = array(
			'success' => true,
			'step' => 5,
			'message' => 'success'
		);

		$this->user['email'] = $this->input->post('admin_email');
		$this->user['password'] = $this->input->post('admin_pw');
		$this->user['password2'] = $this->input->post('admin_pw2');
		$this->user['firstname'] = $this->input->post('admin_firstname');
		$this->user['lastname'] = $this->input->post('admin_lastname');

		try
		{
			if($this->user['password2'] != $this->user['password'])
			{
				throw new InstallException('Your administrative password was not typed correctly.');
			}

			if (strlen($this->user['password']) < VBX_User::MIN_PASSWORD_LENGTH)
			{
				throw new InstallException('Password must be at least '.VBX_User::MIN_PASSWORD_LENGTH.' characters.');
			}

			if (!filter_var($this->user['email'], FILTER_VALIDATE_EMAIL))
			{
				throw new InstallException('Email address is invalid. Please check the address and try again.');
			}

			$required_fields = array(
				'email' => 'Email Address',
				'password' => 'Password',
				'firstname' => 'First Name'
			);
			foreach($required_fields as $required_field => $label)
			{
				if(empty($this->user[$required_field]))
				{
					throw new InstallException('Required field: '.$label);
				}
			}
		}
		catch(InstallException $e)
		{
			$json['success'] = false;
			$json['message'] = $e->getMessage();
		}
		return $json;
	}

	/**
	 * If .htaccess file doesn't exist try to preemptively
	 * create one from the htaccess_dist file. Nothing special,
	 * just try to make a copy of the file. If it doesn't
	 * work it doesn't work.
	 *
	 * @return void
	 */
	protected function pre_test_htaccess()
	{
		if (!is_file(APPPATH.'../.htaccess') && is_writable(APPPATH.'../') && is_file(APPPATH.'../htaccess_dist'))
		{
			$message = 'Trying to copy `htaccess_dist` to `.htaccess`... ';
			$result = @copy(APPPATH.'../htaccess_dist', APPPATH.'../.htaccess');
			$message .= ($result ? 'success' : 'failed');
			log_message($message);
		}
	}

    protected function json_return($data) {
        header('Content-type: application/json');
        echo json_encode($data);
        exit;
    }
}
