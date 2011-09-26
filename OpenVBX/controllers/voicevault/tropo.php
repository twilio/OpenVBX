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

require_once(APPPATH.'libraries/tropo/tropo.class.php');

class Tropo extends MY_Controller {

	private $data = array();
	protected $user_id;

	private $tropo;
	private $tropo_session;
	
	public function __construct()
	{
		parent::__construct();
		$this->config->load('openvbx');
		$this->load->database();
		$this->load->model('vbx_device');

		$this->user_id = $this->session->userdata('user_id');

		$this->tropo = new Tropo;
		try {
			$this->tropo_session = new Session_Tropo;
			$this->session->set_userdata(array('tropo-session'=>
				file_get_contents('php://input')));
			$_COOKIE['tropo_session'] = file_get_contents('php://input');
			set_cookie('tropo_session', 
				file_get_contents('php://input'),
				0);
		} catch (TropoException $e) {
			$sessionData = $this->session->userdata('tropo-session');
			if ($sessionData) {
				// Session not available
				$this->tropo_session = new Session_Tropo(
					$this->session->userdata('tropo-session'));
			}
		}
	}

	public function index()
	{
		redirect('');
	}
}
