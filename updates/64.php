<?php

function runUpdate_64()
{
	runUpdate_64_password_update();
}

function runUpdate_64_password_update()
{
	$ci =& get_instance();
	
	$ci->load->dbforge();
	
	// preparing for longer passwords
	$ci->dbforge->modify_column('users', array(
		'password' => array(
			'name' => 'password',
			'type' => 'VARCHAR',
			'constraint' => 128
		)
	));
	
	$ci->vbx_settings->set('schema-version', '64', 1);
}