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

class Voicemail extends User_Controller {

	protected $response;
	protected $request;

	private $data = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->template->write('title', 'Voicemail');
		$this->section = 'voicemail';
	}

	public function index()
	{
		$data = $this->init_view_data();
		$user = VBX_user::get(array('id' => $this->user_id));
		$data['user'] = $user;

		$this->template->add_js('assets/j/account.js');
		$this->template->add_js('assets/j/devices.js');
		
		$voicemail_value = $data['user']->voicemail;
		$data['voicemail_mode'] = '';
		$data['voicemail_play'] = '';
		$data['voicemail_say'] = '';

		if (!empty($voicemail_value))
		{
		    if (preg_match('/^http/i', $voicemail_value) ||
		        preg_match('/^vbx-audio-upload/i', $voicemail_value))
		    {
		        $data['voicemail_mode'] = 'play';
		        $data['voicemail_play'] = $voicemail_value;
		    }
		    else
		    {
		        $data['voicemail_mode'] = 'say';
		        $data['voicemail_say'] = $voicemail_value;
		    }
		}

		$this->respond('', 'voicemail', $data);
	}

	public function greeting()
	{
		return $this->greeting_handler();
	}
	
	private function greeting_handler()
	{
		switch($this->request_method) 
		{
			case 'POST':
				return $this->save_greeting();
			case 'GET':
				return $this->get_greeting();
		}
	}

	private function get_greeting()
	{
		$user = OpenVBX::getCurrentUser();
		$voicemail_value = $user->voicemail;
		$json['mode'] = '';
		$json['play'] = '';
		$json['say'] = '';

		if (!empty($voicemail_value))
		{
		    if (preg_match('/^http/i', $voicemail_value) ||
		        preg_match('/^vbx-audio-upload/i', $voicemail_value))
		    {
		        $json['mode'] = 'play';
		        $json['play'] = $voicemail_value;
				
				if (preg_match('/^vbx-audio-upload:\/\/(.*)/i', $voicemail_value, $matches))
				{
					// This is a locally hosted file, and we need to return the correct
					// absolute URL for the file.
					$json['play'] = real_site_url("audio-uploads/".$matches[1]);
				}
		    }
		    else
		    {
		        $json['mode'] = 'say';
		        $json['say'] = $voicemail_value;
		    }
		}

		$data['json'] = $json;
		if($this->response_type != 'json')
		{
			return redirect('voicemail');
		}
		$this->respond('', 'voicemail/greeting', $data);
	}
	
	private function save_greeting()
	{
		$data['json'] = array('error' => false, 'message' => '');

		$user = VBX_User::get($this->user_id);
		$user->voicemail = $this->input->post('voicemail');

		try
		{
			$user->save();
		}
		catch(VBX_UserException $e)
		{
			$data['json'] = array(
				'error' => true,
				'message' => $e->getMessage()
			);
		}

		return $data;
	}
	

}