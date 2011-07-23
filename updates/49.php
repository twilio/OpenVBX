<?php
	/* runUpdate_49() updates version, schema and adds field for `application_sid` to each tenant */
	function runUpdate_49() {
		$ci = &get_instance();
		
		// undetermined as to wether each tenant will get an application sid or not
		// $tenants = $ci->db
		//    ->from('tenants')
		//    ->get()->result();
		// 
		// if (count($tenants)) {
		// 	foreach ($tenants as $tenant) {
		// 		$ci->settings->add('application_sid', '', $tenant->id);
		// 	}
		// }
		$ci->settings->add('application_sid', '', 1);
		
		$ci->settings->set('version', '0.93', 1);
		$ci->settings->set('schema-version', '49', 1);
	}
?>