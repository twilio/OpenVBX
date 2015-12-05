<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/

 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 **/


class Welcome extends User_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->template->write('title', 'Welcome');
		$this->section = 'welcome';
		$this->admin_only($this->section);
	}
	
	public function index() {
		$tenant_first_run = $this->settings->get('tenant_first_run', $this->tenant->id);
		$twilio_sid = $this->twilio_sid;

		if (!$tenant_first_run && !in_array($twilio_sid, array('unauthorized_client', 'deauthorized_client'))) {
			redirect('/numbers');
		}

		$connect_base_uri = 'http://www.twilio.com/authorize/';
		if ($this->config->item('connect_base_uri')) {
			$connect_base_uri = $this->config->item('connect_base_uri');
		}

		$data = array(
			'openvbx_js' => array(
				'connect_sid' => $this->vbx_settings->get('connect_application_sid', VBX_PARENT_TENANT),
				'connect_base_uri' => $connect_base_uri
			),
			'title' => 'Welcome'
		);
		
		if ($tenant_sid = $this->vbx_settings->get('twilio_sid', $this->tenant->id)) {
			$data['tenant_sid'] = $tenant_sid;
		}
		$this->load->view('steps', $data);
	}
	
	public function finish() {
		$error = false;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$app_name = 'OpenVBX :: '.$this->tenant->url_prefix;
			
			try {
				$account = OpenVBX::getAccount();
				/** @var Services_Twilio_Rest_Application[] $applications */
				$applications = $account->applications->getIterator(0, 10, array('FriendlyName' => $app_name));

				$application = false;
				foreach ($applications as $_application) {
					if ($_application->friendly_name == $app_name) {
						$application = $_application;
					}
				}
			
				$params = array(
					'FriendlyName' => $app_name,
					'VoiceUrl' => site_url('twiml/dial'),
					'VoiceFallbackUrl' => asset_url('fallback/voice.php'),
					'VoiceMethod' => 'POST',
					'SmsUrl' => '',
					'SmsFallbackUrl' => '',
					'SmsMethod' => 'POST'
				);
			
				if ($application) 
				{
					$application->update($params);
				} 
				else 
				{
					$application = $account->applications->create($app_name, $params);
				}
			
				$this->vbx_settings->add('application_sid', $application->sid, $this->tenant->id);
				$this->vbx_settings->delete('tenant_first_run', $this->tenant->id);
			}
			catch (Exception $e) 
			{
				switch ($e->getCode()) {
					case '20003':
						$error = 'Authentication Failed. Invalid Twilio SID or Token ('.$e->getCode().')';
						break;
					default:
						$error = $e->getMessage().' ('.$e->getCode().')';
				}
			}
		}
		
		if ($error) {
			$data['json'] = array(
				'error' => true,
				'message' => $error
			);
		}
		else {
			$data['json'] = array(
				'error' => false,
				'message' => null
			);
		}
		
		$this->respond('', 'welcome', $data);
	}
}