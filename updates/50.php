<?php

function runUpdate_50() {
	set_time_limit(3600);
	
	require_once('OpenVBX/libraries/twilio.php');

	$ci = &get_instance();

	$ci->load->model('vbx_settings');

	$tenants = $ci->db
		 ->from('tenants')
		 ->get()->result();

	foreach($tenants as $tenant) {
		create_application($tenant->name, $tenant->id);
	}

	/* Upgrade to 1.0 and set the schema-version to 50  */
	$ci->settings->set('version', '1.0', 1);
	$ci->settings->set('schema-version', '50', 1);
}

function create_application($name, $tenant_id) {
	$ci = &get_instance();
	$ci->load->model('vbx_settings');

	$app_name = "OpenVBX :: {$name}";
	$twilio_sid = $ci->vbx_settings->get('twilio_sid', $tenant_id);
	$twilio_token = $ci->vbx_settings->get('twilio_token', $tenant_id);
	
	// Rare event, sid and/or token may be empty 
	if (!empty($twilio_sid) && !empty($twilio_token)) 
	{
		error_log('Processing tenant: '.$tenant_id);
		$account = OpenVBX::getAccount($twilio_sid, $twilio_token);
		$applications = $account->applications->getIterator(0, 1, array('FriendlyName' => $app_name));
		$application = false;
		foreach ($applications as $_application) 
		{
			if ($_application->friendly_name == $app_name) 
			{
				$application = $_application;
			}
		}
		
		$params = array('FriendlyName' => $app_name,
					   'VoiceUrl' => tenant_url('twiml/dial', $tenant_id),
					   'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
					   'VoiceMethod' => 'POST',
					   'SmsUrl' => '',
					   'SmsFallbackUrl' => '',
					   'SmsMethod' => 'POST'
				   );
		
		if (!empty($application)) 
		{
			error_log('Modifying app: '.$app_name);
			$application->update($params);
		}
		else 
		{
			error_log('Creating app: '.$app_name);
			$application = $account->applications->create($app_name, $params);
		}
	
		error_log('Created/Updated app for tenant id: '.$tenant_id.' - Application Sid: '.$application->sid);
		$ci->vbx_settings->add('application_sid', $application->sid, $tenant_id);
	}
	else {
		error_log('Skipped app creation for tenant "'.$tenant_id.'" - incomplete account Sid/Token pair.');
	}

}