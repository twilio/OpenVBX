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

require_once(APPPATH.'libraries/twilio.php');

class Iframe extends User_Controller {

	public function __construct() {
		parent::__construct();
	}

	function index() {
		$data = array(
			'site_title' => 'OpenVBX',
			'iframe_url' => site_url('/messages')
		);
		
		// if the 'last_known_url' cookie is set then we've been redirected IN to frames mode
		if (!empty($_COOKIE['last_known_url'])) {
			$data['iframe_url'] = $_COOKIE['last_known_url'];
			setcookie('last_known_url', '', time() - 3600);
		}

		if (!empty($this->application_sid))
		{
			// look at protocol and serve the appropriate file, https comes from amazon aws
			$tjs_baseurl = 'http'.
							(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '').
							'://static.twilio.com';
			$data['twilio_js'] = $tjs_baseurl.'/libs/twiliojs/1.0/twilio.js';
		}

		$data['client_capability'] = $this->capability->generateToken();
		$data['capability'] = $this->capability;

		$this->load->view('iframe', $data);
	}

}
