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
	protected $twilio_js_version = '1.2';

	function index() {
		$data = $this->init_view_data();
		
		$twilio_js = sprintf('//static.twilio.com/libs/twiliojs/%s/twilio%s.js', 
			$this->twilio_js_version,
			($this->config->item('use_unminimized_js') ? '' : '.min')
		);
		
		$data = array_merge($data, array(
			'site_title' => 'OpenVBX',
			'iframe_url' => site_url('/messages'),
			'users' => $this->get_users(),
			'twilio_js' => $twilio_js,
			'client_capability' => null
		));
		
		// if the 'last_known_url' cookie is set then we've been redirected IN to frames mode
		if (!empty($_COOKIE['last_known_url'])) {
			$data['iframe_url'] = $_COOKIE['last_known_url'];
			set_last_known_url('', time() - 3600);
		}
		
		if (!empty($this->application_sid))
		{
			$user_id = intval($this->session->userdata('user_id'));
			$user = VBX_user::get(array('id' => $user_id));
			$data['client_capability'] = generate_capability_token();
		}

		// internal dev haxies
		if (function_exists('twilio_dev_mods')) {
			$data = twilio_dev_mods($data);
		}
		
		$data['site_rev'] = $this->config->item('site_rev');
		$data['browserphone'] = $this->init_browserphone_data($data['callerid_numbers']);
		
		$this->load->view('iframe', $data);
	}
	
	protected function init_browserphone_data($callerid_numbers)
	{
		// defaults
		$browserphone = array(
			'call_using' => 'browser',
			'caller_id' => '(000) 000-0000',
			'number_options' => array(),
			'call_using_options' => array(
				'browser' => array(
					'title' => 'Your Computer',
					'data' => array()
				)
			),
			'devices' => array()
		);
		
		$default_caller_id = false;
		if (is_array($callerid_numbers) && !empty($callerid_numbers))
		{
			$numbered = $named = array();
			$default_caller_id = current($callerid_numbers)->phone;
			foreach ($callerid_numbers as $number)
			{
				if (normalize_phone_to_E164($number->phone)
						!= normalize_phone_to_E164($number->name))
				{
					$named[$number->phone] = $number->name;
				}
				else
				{
					$numbered[$number->phone] = $number->phone;
				}
			}
			ksort($numbered);
			asort($named);
			$browserphone['number_options'] = $named + $numbered;
		}
		
		$user = VBX_User::get(array('id' => $this->session->userdata('user_id')));
		
		// User preferences
		$browserphone['caller_id'] = $user->setting('browserphone_caller_id', $default_caller_id);
		$browserphone['call_using'] = $user->setting('browserphone_call_using', 'browser');
		
		// Wether the user has an active device to use		
		if (count($user->devices))
		{
			foreach ($user->devices as $device)
			{
				if (strpos($device->value, 'client:') !== false)
				{
					continue;
				}
				$browserphone['call_using_options']['device:'.$device->id] = array(
					'title' => 'Device: '.$device->name,
					'data' => (object) array(
						'number' => format_phone($device->value),
						'name' => $device->name
					)
				);
			}
		}
			
		return $browserphone;
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
