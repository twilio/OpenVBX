<?php

function runUpdate_50() {
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

	$appName = "OpenVBX :: {$name}";
	$twilio_sid = $ci->vbx_settings->get('twilio_sid', $tenant_id);
	$twilio_token = $ci->vbx_settings->get('twilio_token', $tenant_id);
	$twilio = new TwilioRestClient($twilio_sid,
								   $twilio_token,
								   'https://api.twilio.com/2010-04-01');
	$response = $twilio->request("Accounts/{$twilio_sid}/Applications",
								 'GET',
								 array('FriendlyName' => $appName));
	if($response->IsError) {
		if($response->HttpStatus > 400) {
			throw(new Exception($response->ErrorMessage));
		}
	}

	// If we found an existing application, update the urls.
	$foundApp = intval($response->ResponseXml->Applications['total']);
	if($foundApp) {
		$appSid = (string)$response->ResponseXml->Applications->Application->Sid;
		$response = $twilio->request("Accounts/{$twilio_sid}/Applications/{$appSid}",
									 'POST',
									 array('FriendlyName' => $appName,
										   'VoiceUrl' => tenant_url('twiml/dial', $tenant_id),
										   'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
										   'VoiceMethod' => 'POST',
										   'SmsUrl' => '',
										   'SmsFallbackUrl' => '',
										   'SmsMethod' => 'POST',
										   ));
		if($response->IsError) {
			if($response->HttpStatus > 400) {
				throw(new Exception($response->ErrorMessage));
			}
		}

		// Otherwise, lets create a new application for openvbx
	} else {
		$response = $twilio->request("Accounts/{$twilio_sid}/Applications",
									 'POST',
									 array('FriendlyName' => $appName,
										   'VoiceUrl' => tenant_url('twiml/dial', $tenant_id),
										   'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
										   'VoiceMethod' => 'POST',
										   'SmsUrl' => '',
										   'SmsFallbackUrl' => '',
										   'SmsMethod' => 'POST',
										   ));
		if($response->IsError) {
			if($response->HttpStatus > 400) {
				throw(new Exception($response->ErrorMessage));
			}
		}

		$appSid = (string)$response->ResponseXml->Application->Sid;
	}

	// Update the settings for this tenant
	$ci->vbx_settings->add('application_sid', $appSid, $tenant_id);
}