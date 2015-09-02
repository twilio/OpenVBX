<?php

global $_db; // janky? yes. functional? yes.

class OpenVBX_Database_Init {
	
	protected $settings;
	protected $dbh;
	
	public function __construct() {
		$this->init();
	}
	
	public function config() {
		$this->reset_schema();
		$this->insert_content();
	}
	
	/**
	 * this is slow, but compatible
	 *
	 * @throws Exception
	 * @param string $table_name 
	 * @return void
	 */
	public function insert_content($table_name = '') 
	{
		$database_content = $this->get_database_content();
		
		if (!empty($table_name)) 
		{
			if (!empty($database_content[$table_name])) 
			{
				$this->insert_rows($table_name, $database_content[$table_name]);
			}
			else {
				throw new Exception('Database data '.$table_name.' not defined in test data');
			}
		}
		else 
		{
			foreach ($database_content as $table_name => $table_data) {
				if (!empty($table_data)) 
				{
					$this->insert_rows($table_name, $table_data);
				}
			}
		}
	}
	
	public function insert_rows($table, $rows) {
		$keys = array_keys($rows[0]);
		
		$values = array();
		foreach ($rows as $row) {
			foreach ($row as &$value) {
				$value = "'".$this->dbh->real_escape_string($value)."'";
			}
			$values[] .= '('.implode(',', $row).')';
		}
		
		$sql = 'INSERT INTO `'.$table.'` (`'.implode('`,`', $keys).'`) VALUES '.implode(',', $values).';';
		$r = $this->dbh->query($sql);

		if ($this->dbh->error) {
			throw new Exception('Failed to run sql: '.$sql.' :: '.$this->dbh->error.' ('.$this->dbh->errno.')');
		}
	}
	
	public function reset_schema() {
		$sql_file = file_get_contents('../../openvbx.sql');
		$sql_lines = explode(';', $sql_file);
		foreach($sql_lines as $sql)
		{
			$sql = trim($sql);
			if(empty($sql))
			{
				continue;
			}

			$r = $this->dbh->query($sql);
			if($this->dbh->error)
			{
				throw new Exception('Failed to run sql: `'.$sql.'` :: '.$this->dbh->error.' ('.$this->dbh->errno.')');
			}
		}
	}

	public function init() {
		$this->get_settings();
		$this->get_salt();
		
		$host = !empty($this->settings['hostname']) ? $this->settings['hostname'] : 'localhost';
		$username = $this->settings['username'];
		$password = $this->settings['password'];
		$database = $this->settings['database'];
		
		$this->dbh = new mysqli($host, $username, $password, $database);
		if ($this->dbh->connect_error) {
			throw new Exception('mysqli Error: '.$this->dbh->connect_error.' ('.$this->dbh->connect_errno.')');
		}
	}
	
	public function get_settings($key = 'default_test') {
		require_once('../config/database.php');
		$settings = false;
		if (isset($db[$key])) {
			$settings = $db[$key];
		}

		return $this->settings = $settings;
	}
	
	public function get_salt() {
		require_once('../config/openvbx.php');
		return $this->salt = $config['salt'];
	}
	
	public function get_database_content() {
		require('./database_content.php');

		if (!empty($database_content['users']))
		{
			$database_content['users'] = $this->salt_users($database_content['users']);
		}
		
		return $database_content;
	}
	
	/**
	 * @todo update this for new password hashing scheme
	 *
	 * @param array $users
	 * @return array
	 */
	public function salt_users($users) {
		if (is_array($users)) 
		{
			foreach ($users as &$user)
			{
				$user['password'] = sha1($this->salt . $user['password']);
			}
		}
		return $users;
	}
}

$_db = new OpenVBX_Database_Init;
$_db->config();