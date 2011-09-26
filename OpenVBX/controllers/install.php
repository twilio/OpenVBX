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

class Install extends Controller {

	public $tenant;

	public $tests;
	public $pass;

	function Install()
	{
		parent::Controller();
		if(file_exists(APPPATH . 'config/openvbx.php')) $this->config->load('openvbx');

		if(file_exists(APPPATH . 'config/database.php') AND version_compare(PHP_VERSION, '5.0.0', '>=')) {
			$this->load->database();

			redirect('');
		}

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
		/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
		$this->openvbx_settings['tropo_username'] = trim($this->input->post('tropo_username'));
		$this->openvbx_settings['tropo_password'] = trim($this->input->post('tropo_password'));
		$this->openvbx_settings['tropo_hostname'] = trim($this->input->post('tropo_hostname'));
		$this->openvbx_settings['phono_api_key'] = trim($this->input->post('phono_api_key'));
		$this->openvbx_settings['voicevault_username'] = trim($this->input->post('voicevault_username'));
		$this->openvbx_settings['voicevault_password'] = trim($this->input->post('voicevault_password'));
		$this->openvbx_settings['voicevault_config'] = trim($this->input->post('voicevault_config'));
		$this->openvbx_settings['voicevault_organisation'] = trim($this->input->post('voicevault_organisation'));
		$this->openvbx_settings['voicevault_number'] = '';
		/** End Disruptive Technologies code **/
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

		$tplvars = array_merge($tplvars,
							   $this->user,
							   $this->database,
							   $this->openvbx,
							   $this->openvbx_settings
							   );

		return $tplvars;
	}

	private function run_tests()
	{

		$this->tests = array();
		$this->pass = TRUE;

		$this->add_test(version_compare(PHP_VERSION, '5.0.0', '>='),
						'PHP version',
						PHP_VERSION,
						'You must be running at least PHP 5.0; you are using ' . PHP_VERSION);

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
		$this->add_test(function_exists('json_encode'),
						'JSON',
						'supported',
						'missing');

		$apache_version = function_exists('apache_get_version')? apache_get_version() : '';
		$this->add_test(function_exists('apache_request_headers'),
						'Apache version',
						preg_replace('/[^0-9.]/', '', $apache_version),
						'missing, but optional',
						false);

		$this->add_test(is_writable(APPPATH . 'config'),
						'Config Dir',
						'writable',
						'permission denied: '. APPPATH . 'config');
		$this->add_test(is_writable(APPPATH . '../audio-uploads'),
						'Upload Dir',
						'writable',
						'permission denied: '. APPPATH . '../audio-uploads');
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
		$this->tests[] = array('name' => $name,
							   'pass' => $pass,
							   'required' => $required,
							   'message' => ($pass ? $pass_text : $fail_text));

		if($required) $this->pass = $this->pass && $pass;
	}

	private function get_database_params($database)
	{
		$database = $this->database;

		$database['dbdriver'] = 'mysql';
		$database['dbprefix'] = '';
		$database['pconnect'] = FALSE;
		$database['db_debug'] = TRUE;
		$database["cache_on"] = FALSE;
		$database["cachedir"] = "";
		$database["char_set"] = "utf8";
		$database["dbcollat"] = "utf8_general_ci";

		return array('global' => array('active_group' => "default",
									   'active_record' => TRUE,
									   ),
					 'default' => $database);
	}

	public function setup()
	{
		$tplvars = $this->input_args();

		$this->run_tests();
		
		if ($this->openvbx_settings['twilio_sid'])
			$this->openvbx_settings['application_sid'] = $this->get_application($this->openvbx_settings);
		
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
			if(!($dbh = @mysql_connect($database['default']['hostname'],
									   $database['default']['username'],
									   $database['default']['password'])))
			{
				throw new InstallException( "Failed to connect to database: "
											. mysql_error(), 2 );
			}

			$this->setup_database($database, $dbh);

			$this->setup_config($database, $openvbx);

			$this->setup_user($user);

			$this->setup_openvbx_settings($openvbx_settings);


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

		echo json_encode($json);
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
				throw new InstallException( "Failed to run sql: ".$sql. " :: ". mysql_error($dbh), 2);
			}
		}

	}

	private function setup_config($database, $openvbx)
	{
		$this->write_config(APPPATH. 'config/database.php', $database, 'db');
		$this->write_config(APPPATH. 'config/openvbx.php', $openvbx, 'config');

		if(!is_file(APPPATH. 'config/database.php')
		   || !is_file(APPPATH. 'config/openvbx.php'))
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
		$admin->online = 9;

		try
		{
			$admin->save();
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
					throw new InstallException( "Failed to create setting for $key. Please re-create database", 0);
				}
			}
		}
		catch(SettingsException $e)
		{
			throw new InstallException( 'Unable to setup valid instance.  Please re-create your database');
		}
	}

	private function get_application($settings) {
		require_once('OpenVBX/libraries/twilio.php');

		try
		{
			$twilio_sid = $settings['twilio_sid'];
			$twilio_token = $settings['twilio_token'];
			$appToken = md5($_SERVER['REQUEST_URI']);
			$appName = "OpenVBX - {$appToken}";
			$twilio = new TwilioRestClient($twilio_sid, $twilio_token);

			// Check to see if the application already exists
			$response = $twilio->request("Accounts/{$twilio_sid}/Applications",
										 'GET',
										 array('FriendlyName' => $appName));
			if($response->IsError) {
                if($response->HttpStatus > 400) {
					throw(new InstallException($response->ErrorMessage));
                }
			}

			$foundApp = intval($response->ResponseXml->Applications['total']);
			if($foundApp) {
                $appSid = (string)$response->ResponseXml->Applications->Application->Sid;
				$response = $twilio->request("Accounts/{$twilio_sid}/Applications/{$appSid}",
											 'POST',
											 array('FriendlyName' => $appName,
												   'VoiceUrl' => site_url('twiml/dial'),
												   'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
												   'VoiceMethod' => 'POST',
												   'SmsUrl' => '',
												   'SmsFallbackUrl' => '',
												   'SmsMethod' => 'POST',
												   ));

				return $appSid;
			}

			$response = $twilio->request("Accounts/{$twilio_sid}/Applications",
										 'POST',
										 array('FriendlyName' => $appName,
											   'VoiceUrl' => site_url('/twiml/dial'),
											   'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
											   'VoiceMethod' => 'POST',
											   'SmsUrl' => '',
											   'SmsFallbackUrl' => '',
											   'SmsMethod' => 'POST',
											   ));
			if($response->IsError) {
                if($response->HttpStatus > 400) {
					$json['errors'] = array('twilio_sid' => $response->ErrorMessage,
											'twilio_token' => $response->ErrorMessage );

					throw new InstallException('Invalid Twilio SID or Token');
                }

                throw new InstallException($response->ErrorMessage);
			}

			$appSid = (string)$response->ResponseXml->Application->Sid;

		}
		catch(Exception $e)
		{
			throw new InstallException($e->getMessage());
		}

		return $appSid;
	}

	function validate()
	{
		$step = $this->input->post('step');
		$json = array('success' => true);
		if($step == 1) {
			echo json_encode($json);
			return;
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
		echo json_encode($json);
	}

	function validate_step2()
	{
		$json = array('success' => true, 'step' => 2, 'message' => 'success');

		$database = $this->get_database_params($this->database);

		try
		{
			if(!($dbh = @mysql_connect($database['default']['hostname'],
									   $database['default']['username'],
									   $database['default']['password'])))
			{
				$error = mysql_error();
				$json['errors'] = array('hostname' => $error,
										'username' => '',
										'password' => '');
				throw new InstallException( "Failed to connect to database: $error",
											2 );
			}

			if(!mysql_select_db($database['default']['database'], $dbh))
			{
				$error = mysql_error($dbh);
				$json['errors'] = array('database_name' => $error );
				throw new InstallException( "Failed to access database: $error",
											2);
			}

		}
		catch(InstallException $e)
		{
			$json['success'] = false;
			$json['message'] = $e->getMessage();
			$json['step'] = $e->getCode();
		}

		return $json;
	}

	function validate_step3()
	{
		$json = array('success' => true, 'step' => 2, 'message' => 'success');
		$twilio_sid = $this->openvbx_settings['twilio_sid'];
		$twilio_token = $this->openvbx_settings['twilio_token'];
		$tropo_username = $this->openvbx_settings['tropo_username'];
		$tropo_password = $this->openvbx_settings['tropo_password'];

		if (!($twilio_sid && $twilio_token) &&
			!($tropo_username && $tropo_password)) {
			$json['success'] = false;
			$json['message'] = "You must have either a Twilio or Tropo account.";
			return $json;
		}

		require_once(APPPATH . 'libraries/twilio.php');

		if ($twilio_sid && $twilio_token) {
			try
			{
				$twilio = new TwilioRestClient($twilio_sid,
											   $twilio_token);

				$response = $twilio->request("Accounts/{$twilio_sid}",
											 'GET',
											 array());

				if($response->IsError) {
					if($response->HttpStatus > 400) {
						$json['errors'] = array('twilio_sid' => $response->ErrorMessage,
												'twilio_token' => $response->ErrorMessage );

						throw new InstallException('Invalid Twilio SID or Token');
					}

					throw new InstallException($response->ErrorMessage);
				}

			}
			catch(InstallException $e)
			{
				$json['success'] = false;
				$json['message'] = $e->getMessage();
				$json['step'] = $e->getCode();
			}
		}

		/** Updated, Disruptive Technologies, for Tropo VBX conversion **/

		require_once(APPPATH . 'libraries/tropo/tropo.class.php');

		if ($tropo_username && $tropo_password) {

			// Tropo User Account details

			try {
				// Get (or create) the Tropo application
				$provisioner = new ProvisioningAPI($tropo_username, $tropo_password);
				$applications = json_decode($provisioner->viewApplications());
			} catch (Exception $e) {
				// Invalid tropo account
				$json['success'] = false;
				$json['message'] = 'Invalid Tropo credentials.';
			}

		}

		/** End Disruptive Technologies code **/

		return $json;
	}

	function validate_step4()
	{
		$json = array('success' => true, 'step' => 4, 'message' => 'success');
		$this->openvbx_settings['from_email'] = trim($this->input->post('from_email'));

		try
		{
			foreach(array('from_email' => 'Notification Sender Email Address') as $required_field => $label)
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
			$json['step'] = $e->getCode();
		}
		return $json;
	}

	function validate_step5()
	{
		$json = array('success' => true, 'step' => 2, 'message' => 'success');

		$this->user['email'] = $this->input->post('admin_email');
		$this->user['password'] = $this->input->post('admin_pw');
		$this->user['password2'] = $this->input->post('admin_pw2');
		$this->user['firstname'] = $this->input->post('admin_firstname');
		$this->user['lastname'] = $this->input->post('admin_lastname');

		try
		{
			if($this->user['password2'] != $this->user['password'])
				throw new InstallException('Your administrative password was not typed correctly.');

			foreach(array('email' => 'Email Address',
						  'password' => 'Password',
						  'firstname' => 'First Name') as $required_field => $label)
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
			$json['step'] = $e->getCode();
		}
		return $json;
	}
}
