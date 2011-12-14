<?php

function runUpdate_68()
{
	runUpdate_68_create_cache_table();
	runUpdate_68_update_users();
	runUpdate_68_alter_users_table();
	
	$ci =& get_instance();
	$ci->settings->set('schema-version', '68', 1);
}

function runUpdate_68_create_cache_table()
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

function runUpdate_68_update_users()
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

function runUpdate_68_alter_users_table()
{	
	$ci =& get_instance();
	$ci->load->dbforge();
	
	$columns = array(
		'online',
		'last_seen',
		'last_login'
	);
	
	foreach ($columns as $column)
	{
		if ($ci->db->field_exists($column, 'users'))
		{
			$ci->dbforge->drop_column('users', $column);
		}		
	}
}