<?php

/* runUpdate_45() updates Twilio Account to 2010 */
function runUpdate_45() {
	set_time_limit(3600);
	
	$ci = &get_instance();
	$tenants = $ci->db
		->from('tenants')
		->get()->result();
	
	if (count($tenants)) {
		$ci->load->model('vbx_incoming_numbers');
		$numbers = $ci->vbx_incoming_numbers->get_numbers();
		foreach ($tenants as $tenant) {
			error_log("Updating to 2010: ". var_export($tenant, true));
			$twilio_sid = $ci->settings->get('twilio_sid', $tenant->id);
			$twilio_token = $ci->settings->get('twilio_token', $tenant->id);
			
			if (!empty($twilio_sid) && !empty($twilio_token))
			{
				$account = OpenVBX::getAccount($twilio_sid, $twilio_token);				
				foreach ($account->incoming_phone_numbers as $number) 
				{
					$number->update(array(
						'ApiVersion' => '2010-04-01'
					));
				}
			}
			else {
				error_log('Skipped number updates for tenant "'.$tenant->id.'" - incomplete account Sid/Token pair.');
			}
		}
	}

    $ci->settings->set('schema-version', '45', 1);
}
