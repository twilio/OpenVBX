<?php

/**
 * Update tenants to include account type information
 * Rev schema & application versions
 *
 * @return void
 */
function runUpdate_55() {
	$ci =& get_instance();
	runUpdate_55_add_tenant_type_field();
	runUpdate_55_update_tenant_type_status();
	$ci->vbx_settings->set('version', '1.1b2', 1);
	$ci->vbx_settings->set('schema-version', '55', 1);
}

/**
 * Add `type` field to `tenants` table
 *
 * @return void
 */
function runUpdate_55_add_tenant_type_field() {					
	$ci = &get_instance();
	if (!$ci->db->field_exists('type', 'tenants')) {
		$ci->load->dbforge();
		$ci->dbforge->add_column('tenants', array(
			'type' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'default' => '0'
			)
		));
	}
}

/**
 * Update each tenant to sub-account status
 * Pull full list of sub-accounts to make sure that accounts being upgraded are sub-accounts
 * Any account found to not be a sub-account of tenant 1 is assumed to be a full account
 * 
 * @return void
 */
function runUpdate_55_update_tenant_type_status() {
	$ci = &get_instance();
	
	$parent_account_sid = $ci->vbx_settings->get('twilio_sid', 1);
	$parent_account_token = $ci->vbx_settings->get('twilio_token', 1);
	$parent_account = OpenVBX::getAccount($parent_account_sid, $parent_account_token);
	
	$subaccount_sids = array();
	foreach ($parent_account->accounts as $account) {
		array_push($subaccount_sids, $account->sid);
	}
	
	$tenants = $ci->db
		 ->from('tenants')
		 ->where('id >', '1') // exclude the host account
		 ->get()
		 ->result();
	
	if (!empty($tenants)) {
		foreach($tenants as $tenant) {
			$tenant_sid = $ci->vbx_settings->get('twilio_sid', $tenant->id);
			$tenant_token = $ci->vbx_settings->get('twilio_token', $tenant->id);
		
			if (in_array($tenant_sid, $subaccount_sids)) {
				// tenant is a sub-account of the parent
				$type = 2;
			}
			else {
				// tenant is a regular account, not a sub-account
				// may still be a sub of someone else, but not of this parent
				$type = 1;
			}
		
			$ci->db
				->set('type', $type)
				->where('id', $tenant->id)
				->update('tenants');
		}		
	}
}
