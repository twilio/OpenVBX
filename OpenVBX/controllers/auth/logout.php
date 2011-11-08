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

class Logout extends MY_Controller
{
	protected $user_id;
	protected $js_assets = 'loginjs';

	function __construct()
	{
		parent::__construct();
		$this->config->load('openvbx');
		$this->load->database();

		$ci =& get_instance();
		$ci->cache->enabled(false);
		
		$this->user_id = $this->session->userdata('user_id');
	}

	public function index()
	{
		return $this->logout();
	}
	
	private function logout()
	{
		$this->session->sess_destroy();
		$this->session->set_userdata('loggedin', false);
		
		$data = array('error' => 'You have been logged out.',
					  'redirect' => '',
					  );
		
		return $this->respond('Log Out', 'login', $data, 'login-wrapper', 'layout/login');
	}

}