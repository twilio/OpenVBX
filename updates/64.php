<?php

function runUpdate_64()
{
	runUpdate_64_create_cache_table();
	runUpdate_64_update_users();
	runUpdate_64_alter_users_table();
	
	$ci =& get_instance();
	$ci->settings->set('version', '1.2b-object-cache', 1);
	$ci->settings->set('schema-version', '64', 1);
}

function runUpdate_64_create_cache_table()
{
	$sql = trim("
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL default '',
  `group` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  `tenant_id` int(11) NOT NULL,
  PRIMARY KEY  (`key`,`group`,`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");

	$ci =& get_instance();
	$ci->db->query($sql);
}

function runUpdate_64_update_users()
{
	$ci =& get_instance();
	$users = $ci->db
		->from('users')
		->get()->result();

	if (!empty($users))
	{
		foreach ($users as $user)
		{
			$_user = new VBX_User($user);
			foreach (array('online', 'last_seen', 'last_login') as $setting)
			{
				if (!is_null($user->$setting))
				{
					$_user->setting_set($setting, $user->$setting);
				}
			}
		}
	}
}

function runUpdate_64_alter_users_table()
{	
	$ci =& get_instance();
	$ci->load->dbforge();
	$ci->dbforge->drop_column('users', 'online');
	$ci->dbforge->drop_column('users', 'last_seen');
	$ci->dbforge->drop_column('users', 'last_login');
}