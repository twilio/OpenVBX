<?php

function runUpdate_65()
{
	runUpdate_64_password_update();
	
	$ci =& get_instance();
	$ci->vbx_settings->set('schema-version', '65', 1);
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
}

function runUpdate_64_add_dial_timeout()
{
	$ci =& get_instance();
	$tenants = $ci->db
		->from('tenants')
		->get()->result();
		
	if (count($tenants))
	{
		foreach ($tenants as $tenant)
		{
			$ci->vbx_settings->set('dial_timeout', 15, $tenant->id);
		}
	}
}