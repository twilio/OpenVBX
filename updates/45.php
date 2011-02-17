<?php

/* runUpdate_45() updates Twilio Account to 2010 */
function runUpdate_45() {
	  $ci = &get_instance();
	  $tenants = $ci->db
		   ->from('tenants')
		   ->get()->result();
	  $ci->load->model('vbx_incoming_numbers');
	  $numbers = $ci->vbx_incoming_numbers->get_numbers($retrieve_sandbox = true);
	  foreach($tenants as $tenant) {
		  error_log("Updating to 2010: ". var_export($tenant, true));
		  $twilio_sid = $ci->settings->get('twilio_sid', $tenant->id);
		  $twilio_token = $ci->settings->get('twilio_token', $tenant->id);
		  $twilio = new TwilioRestClient($twilio_sid,
										 $twilio_token,
										 'https://api.twilio.com/2010-04-01');
		  $response = $twilio->request("Accounts/{$twilio_sid}/IncomingPhoneNumbers");
		  $numberUrls = array();
		  if(isset($response->ResponseXml->IncomingPhoneNumbers->IncomingPhoneNumber))
		  {
			  $xmlPhoneNumbers = $response->ResponseXml->IncomingPhoneNumbers->IncomingPhoneNumber
				  ? $response->ResponseXml->IncomingPhoneNumbers->IncomingPhoneNumber
				  : array($response->ResponseXml->IncomingPhoneNumbers->IncomingPhoneNumber);
			  foreach($xmlPhoneNumbers as $number)
			  {
				  $numberUrls[] = preg_replace('#/20[0-9]{2}-\d{2}-\d{2}/#', '', (string)$number->Uri);
			  }
		  }

		  foreach($numberUrls as $url) {
			  error_log("updating $url");
			  $response = $twilio->request($url,
										   'POST',
										   array('ApiVersion' => '2010-04-01'));
		  }
	  }

      $ci->settings->set('schema-version', '45', 1);
}