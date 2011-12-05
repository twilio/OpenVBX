<?php

function runUpdate_67()
{
	runUpdate_67_notifications_settings();
	
	$ci =& get_instance();
	$ci->vbx_settings->set('schema-version', '67', 1);
}

function runUpdate_67_notifications_settings()
{
	$ci =& get_instance();
	$tenants = $ci->db
		->from('tenants')
		->get()->result();

	if (count($tenants))
	{
		foreach ($tenants as $tenant)
		{
			$ci->vbx_settings->add('email_notifications_voice', 1, $tenant->id);
			$ci->vbx_settings->add('email_notifications_sms', 1, $tenant->id);
		}
	}
}