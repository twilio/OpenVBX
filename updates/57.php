<?php

function runUpdate_57()
{
	$ci =& get_instance();
	
	$tenants = $ci->db
		 ->from('tenants')
		 ->get()->result();

	if (!empty($tenants))
	{
		foreach($tenants as $tenant) 
		{
			$ci->vbx_settings->add('transcriptions', '1', $tenant->id);
			$ci->vbx_settings->add('voice', 'man', $tenant->id);
			$ci->vbx_settings->add('voice_language', 'en', $tenant->id);
		}
	}
	
	$ci->vbx_settings->set('schema-version', '57', 1);
}