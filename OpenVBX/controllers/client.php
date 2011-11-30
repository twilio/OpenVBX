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

class ClientException extends Exception {}
/*
 Client handles all public access information for determining version, theming, i18n.
*/
class Client extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();	
	}

	public function index()
	{
		switch($this->request_method)
		{
			case 'GET':
				return $this->get_client();
				break;
			case 'POST':
			case 'DELETE':
			default:
				break;
		}
	}

	public function updates()
	{
		switch($this->request_method)
		{
			case 'GET':
				return $this->get_updates();
			default:
				break;
		}
	}

	private function get_updates()
	{
		if($this->session->userdata('loggedin') != 1
		   || $this->session->userdata('is_admin') != 1)
		{
			$data['json'] = array('message' => 'Unable to fetch updates', 'error' => true);
			return $this->respond('', '', $data);
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://openvbx.org/updates/latest.json');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$resp = curl_exec($ch);
		
		if(!$resp)
		{
			// Its okay we can't connect to the update system but log it
			error_log('Unable to connect to OpenVBX Update notification server');
		}
		
		$data['json'] = array('message' => 'Unable to fetch updates', 'error' => true);
		
		if($obj = json_decode($resp))
		{
			$data['json']['upgradeAvailable'] = false;
			list($current['major'], $current['minor']) = explode('.', OpenVBX::version());
			list($latest['major'], $latest['minor']) = explode('.', $obj->version);
			if($latest['major'] > $current['major']
			   || $latest['major'] == $current['major'] && $latest['minor'] > $current['minor'])
			{
				$data['json'] = array(
					'error' => false,
					'upgradeAvailable' => true
				);
			}
		}

		$this->respond('', '', $data);
	}

	private function get_client()
	{
		$theme_type = $this->input->get('type');
		$with_i18n = $this->input->get('with_i18n', 0);
		
		try
		{			
			$client = array(
				'error' => false,
				'message' => '',
				'version' => $this->get_version(),
				'theme' => $this->get_theme($theme_type),
			);

			if($with_i18n)
			{
				$client['i18n'] = $this->get_i18n();
			}
		}
		catch(ClientException $e)
		{
			$client['message'] = $e->getMessage();
			$client['error'] = true;
		}
		
		if($this->response_type != 'json')
		{
			return redirect('');
		}
		
		$data['json'] = $client;
		return $this->respond('', 'client', $data);
	}
	
	private function get_version()
	{
		return OpenVBX::version();
	}

	private function get_theme($type)
	{
		$this->load->model('vbx_theme');
		$theme = $this->settings->get('theme', $this->tenant->id);
		$client_theme = null;
		
		switch($type)
		{
			case 'iphone':
				$client_theme = json_decode($this->vbx_theme->get_iphone_json($theme));
				break;
		}

		return $client_theme;
	}

	private function get_i18n()
	{
		return array();
	}
}
