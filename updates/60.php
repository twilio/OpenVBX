<?php

	function runUpdate_60() {
		$ci =& get_instance();
		set_time_limit(3600);

		$tenants = $ci->db
			 ->from('tenants')
			 ->get()->result();

		foreach($tenants as $tenant) {
			$ci->vbx_settings->add('numbers_country', 'US', $tenant->id);
			$ci->vbx_settings->add('gravatars', 0, $tenant->id);
		}

		$ci->vbx_settings->set('schema-version', '60', 1);
	}