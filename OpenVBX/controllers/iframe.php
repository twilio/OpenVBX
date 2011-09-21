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

#require_once(APPPATH.'libraries/twilio.php');

class Iframe extends User_Controller {

	protected $client_token_timeout;
	
	protected $tjs_baseurl = '';
	protected $tjs_file = '';

	public function __construct() {
		parent::__construct();

		// look at protocol and serve the appropriate file, https comes from amazon aws
		$this->twilio_js_baseurl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ?
			'https://s3.amazonaws.com/static.twilio.com' : 'http://static.twilio.com';
		$this->twilio_js_baseurl = 'http://static.stage.twilio.com';
		$this->twilio_js_file = 'twilio'.($this->config->item('use_unminimized_js') ? '' : '.min').'.js';
	}

	function index() {
		$data = $this->init_view_data();
		$data = array_merge($data, array(
			'site_title' => 'OpenVBX',
			'iframe_url' => site_url('/messages'),
			'users' => $this->get_users(),
			'twilio_js' => $this->twilio_js_baseurl.'/libs/twiliojs/1.0/'.$this->twilio_js_file,
			'client_capability' => null
		));
		
		// if the 'last_known_url' cookie is set then we've been redirected IN to frames mode
		if (!empty($_COOKIE['last_known_url'])) {
			$data['iframe_url'] = $_COOKIE['last_known_url'];
			setcookie('last_known_url', '', time() - 3600, '/');
		}

		if (!empty($this->application_sid))
		{
			$user_id = intval($this->session->userdata('user_id'));
			$user = VBX_user::get(array('id' => $user_id));
			$data['client_capability'] = generate_capability_token($this->make_rest_access(), ($user->online == 1));
		}

		// internal dev haxies
		if (function_exists('twilio_dev_mods')) {
			$data = twilio_dev_mods($data);
		}
		
		$this->load->view('iframe', $data);
	}
	
	protected function get_users() {
		$users = VBX_User::search(array(
			'is_active' => 1,
		));
		
		$current_user = $this->session->userdata('user_id');
		foreach ($users as $k => $user) {
			if ($user->id == $current_user) {
				unset($users[$k]);
			}
		}
		
		return $users;
	}
}
