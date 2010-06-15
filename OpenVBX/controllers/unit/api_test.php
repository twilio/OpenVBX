<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

class API_TestException extends Exception {}

class API_Test extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->section = 'api tester';
		$this->admin_only($this->section);
		$this->load->library('unit_test');
		$this->load->config('openvbx');
		$this->test_pass = $this->config->item('test_pass');

		if(empty($this->test_pass))
		{
			throw new API_TestException('API Password not set.');
		}
	}

	function index()
	{
		$methods = get_class_methods($this);
		foreach($methods as $method)
		{
			if(preg_match('/^test_(.*)$/', $method, $matches))
			{
				$this->$method();
			}
		}
		
		$data['result'] = $this->unit->result();
		$data['title'] = get_class($this);
		$this->respond('', 'unit', $data);
	}

	function single($test= '')
	{
		$this->{"test_".$test}();
		$data['result'] = $this->unit->result();
		$data['title'] = get_class($this).'::test_'.$test;
		$this->respond('', 'unit', $data);
	}

	private function run($group, $title, $expected, $test)
	{
		$this->unit->run($expected, $test, '['. $group . '] ' . $title);
	}

	private function test_multi_user_route()
	{
		$group = 'GET multi';
		$result = $this->get_url(site_url('multi'),
								 array());
		$this->run($group,
				   'Test multi-tenant root redirect',
				   false,
				   preg_match('/error: 404/', $result));
	}

	private function test_message_details_single_user()
	{
		$group = 'GET messages/details/(:num)';
		$message = $this->get_url(site_url('messages/details/113'), array());
		$message = json_decode($message);

		$this->run($group, 'message received', true, is_object($message));

		$group = 'GET messages/details/(:num)/annotations';
		$annotations = $this->get_url(site_url('messages/details/113/annotations'),
									  array());
		$annotations = json_decode($annotations);
		$this->run($group, 'annotations received', true, is_object($annotations));

		$group = 'POST messages/details/(:num)/annotations';
		$annotations = $this->post_url(site_url('messages/details/113/annotations'),
									   array(
											 'annotation_type' => 'noted',
											 'description' => "Unit Test note",
											 ));

		$annotations = json_decode($annotations);
		$this->run($group, 'new annotation received', true,
				   is_object($annotations) && is_object($annotations->annotation));

		$group = 'GET messages/details/(:num)/callback';
		$callback = $this->get_url(site_url('messages/details/113/callback'), array());
		$callback = json_decode($callback);
		
		$this->run($group, 'callback received', true, is_object($callback));

		$group = 'POST messages/details/(:num)';
		$message = $this->post_url(site_url('messages/details/113'),
								   array('assigned'));
		
		$assigned_response = json_decode($message);
		$this->run($group, 'assigned response error property', true,
				   isset($assigned_response->error));
		$this->run($group, 'assigned response message property', true,
				   isset($assigned_response->message));
		$this->run($group, 'assigned user - error = false', false,
				   (isset($assigned_response->error)?
					$assigned_response->error : true));
		$this->run($group, 'assigned user - message = ""', '',
				   (isset($assigned_response->error)?
					$assigned_response->message : 'message property does not exist'));
	}
	
	private function test_message_details_multi_user()
	{
		$group = 'GET multi/messages/details/(:num)';
		$message = $this->get_url(site_url('multi/messages/details/113'), array());
		$message = json_decode($message);
		$this->run($group, 'message received', true, is_object($message));
		
		$group = 'GET multi/messages/details/(:num)/annotations';
		$annotations = $this->get_url(site_url('multi/messages/details/113/annotations'),
									  array());
		$annotations = json_decode($annotations);

		$this->run($group, 'annotations received', true, is_object($annotations));

		$group = 'POST multi/messages/details/(:num)/annotations';
		$annotations = $this->post_url(site_url('multi/messages/details/113/annotations'),
									   array(
											 'annotation_type' => 'noted',
											 'description' => "Unit Test note",
											 ));

		$annotations = json_decode($annotations);
		$this->run($group, 'new annotation received', true,
				   is_object($annotations) && is_object($annotations->annotation));

		$group = 'GET multi/messages/details/(:num)/callback';
		$callback = $this->get_url(site_url('multi/messages/details/113/callback'),
								   array());
		$callback = json_decode($callback);

		$this->run($group, 'callback received', true, is_object($callback));

		$group = 'POST multi/messages/details/(:num)';
		$message = $this->post_url(site_url('multi/messages/details/113'),
								   array('assigned' => 1,
										 'ticket_status' => 'closed'));
		
		$assigned_response = json_decode($message);
		
		$this->run($group, 'assigned response error property', true,
				   isset($assigned_response->error));
		$this->run($group, 'assigned response message property', true,
				   isset($assigned_response->message));
		$this->run($group, 'assigned user - error = false', false,
				   (isset($assigned_response->error)?
					$assigned_response->error : true));
		$this->run($group, 'assigned user - message = ""', '',
				   (isset($assigned_response->error)?
					$assigned_response->message : 'message property does not exist'));
	}

	private function test_message_details_api_single_user()
	{
		$group = 'GET api/2009-12-01/messages/details/(:num)';
		$message = $this->get_url(site_url('api/2009-12-01/messages/details/113'), array());
		$message = json_decode($message);
		$this->run($group, 'message received', true, is_object($message));
		
		$group = 'GET api/2009-12-01/messages/details/(:num)/annotations';
		$annotations = $this->get_url(site_url('api/2009-12-01/messages/details/113/annotations'),
									  array());
		$annotations = json_decode($annotations);

		$this->run($group, 'annotations received', true, is_object($annotations));

		$group = 'POST api/2009-12-01/messages/details/(:num)/annotations';
		$annotations = $this->post_url(site_url('api/2009-12-01/messages/details/113/annotations'),
									   array(
											 'annotation_type' => 'noted',
											 'description' => "Unit Test note",
											 ));

		$annotations = json_decode($annotations);
		$this->run($group, 'new annotation received', true,
				   is_object($annotations) && is_object($annotations->annotation));

		$group = 'GET api/2009-12-01/messages/details/(:num)/callback';
		$callback = $this->get_url(site_url('api/2009-12-01/messages/details/113/callback'),
								   array());
		$callback = json_decode($callback);

		$this->run($group, 'callback received', true, is_object($callback));

		$group = 'POST api/2009-12-01/messages/details/(:num)';
		$message = $this->post_url(site_url('api/2009-12-01/messages/details/113'),
								   array('assigned' => 1,
										 'ticket_status' => 'closed'));
		
		$assigned_response = json_decode($message);
		
		$this->run($group, 'assigned response error property', true,
				   isset($assigned_response->error));
		$this->run($group, 'assigned response message property', true,
				   isset($assigned_response->message));
		$this->run($group, 'assigned user - error = false', false,
				   (isset($assigned_response->error)?
					$assigned_response->error : true));
		$this->run($group, 'assigned user - message = ""', '',
				   (isset($assigned_response->error)?
					$assigned_response->message : 'message property does not exist'));
	}

	private function test_message_details_api_multi_user()
	{
		$group = 'GET multi/api/2009-12-01/messages/details/(:num)';
		$message = $this->get_url(site_url('multi/api/2009-12-01/messages/details/113'), array());
		$message = json_decode($message);
		$this->run($group, 'message received', true, is_object($message));
		
		$group = 'GET multi/api/2009-12-01/messages/details/(:num)/annotations';
		$annotations = $this->get_url(site_url('multi/api/2009-12-01/messages/details/113/annotations'),
									  array());
		$annotations = json_decode($annotations);

		$this->run($group, 'annotations received', true, is_object($annotations));

		$group = 'POST multi/api/2009-12-01/messages/details/(:num)/annotations';
		$annotations = $this->post_url(site_url('multi/api/2009-12-01/messages/details/113/annotations'),
									   array(
											 'annotation_type' => 'noted',
											 'description' => "Unit Test note",
											 ));

		$annotations = json_decode($annotations);
		$this->run($group, 'new annotation received', true,
				   is_object($annotations) && is_object($annotations->annotation));

		$group = 'GET multi/api/2009-12-01/messages/details/(:num)/callback';
		$callback = $this->get_url(site_url('multi/api/2009-12-01/messages/details/113/callback'),
								   array());
		$callback = json_decode($callback);

		$this->run($group, 'callback received', true, is_object($callback));

		$group = 'POST multi/api/2009-12-01/messages/details/(:num)';
		$message = $this->post_url(site_url('multi/api/2009-12-01/messages/details/113'),
								   array('assigned' => 1,
										 'ticket_status' => 'closed'));
		
		$assigned_response = json_decode($message);
		
		$this->run($group, 'assigned response error property', true,
				   isset($assigned_response->error));
		$this->run($group, 'assigned response message property', true,
				   isset($assigned_response->message));
		$this->run($group, 'assigned user - error = false', false,
				   (isset($assigned_response->error)?
					$assigned_response->error : true));
		$this->run($group, 'assigned user - message = ""', '',
				   (isset($assigned_response->error)?
					$assigned_response->message : 'message property does not exist'));
	}
	
	private function test_message_inbox_single_user()
	{
		$group = 'GET messages/inbox/';
		$folders = $this->get_url(site_url('messages/inbox'), array());
		$folders = json_decode($folders);

		$this->run($group, 'folders received', true, is_object($folders));
		
		$group = 'GET messages/inbox/(:num)';
		$inbox = $this->get_url(site_url('messages/inbox/0'), array());
		$inbox = json_decode($inbox);
		
		$this->run($group, 'inbox received', true, is_object($inbox));
	}

	private function test_message_inbox_api_single_user()
	{
		$group = 'GET api/2009-12-01/messages/inbox/';
		$folders = $this->get_url(site_url('api/2009-12-01/messages/inbox'), array());
		$folders = json_decode($folders);

		$this->run($group, 'folders received', true, is_object($folders));
		
		$group = 'GET api/2009-12-01/messages/inbox/(:num)';
		$inbox = $this->get_url(site_url('api/2009-12-01/messages/inbox/0'), array());
		$inbox = json_decode($inbox);
		
		$this->run($group, 'inbox received', true, is_object($inbox));
	}

	private function test_message_inbox_multi_user()
	{
		$group = 'GET multi/messages/inbox/';
		$folders = $this->get_url(site_url('multi/messages/inbox'), array());
		$folders = json_decode($folders);
		
		$this->run($group, 'folders received', true, is_object($folders));

		$group = 'GET multi/messages/inbox/(:num)';
		$inbox = $this->get_url(site_url('multi/messages/inbox/0'), array());
		$inbox = json_decode($inbox);
		
		$this->run($group, 'inbox received', true, is_object($inbox));
	}

	private function test_message_inbox_api_multi_user()
	{
		$group = 'GET multi/api/2009-12-01/messages/inbox/';
		$folders = $this->get_url(site_url('multi/api/2009-12-01/messages/inbox'), array());
		$folders = json_decode($folders);

		$this->run($group, 'folders received', true, is_object($folders));
		
		$group = 'GET multi/api/2009-12-01/messages/inbox/(:num)';
		$inbox = $this->get_url(site_url('multi/api/2009-12-01/messages/inbox/0'), array());
		$inbox = json_decode($inbox);

		$this->run($group, 'inbox received', true, is_object($inbox));

		/* Test empty messages */
		$inbox = $this->get_url(site_url('multi/api/2009-12-01/messages/inbox/8'), array());
		$inbox = json_decode($inbox);
		$this->run($group, 'empty inbox list',
				   true,
				   (isset($inbox->messages->total)
					&& $inbox->messages->total == 0)
				   );
	}

	private function test_numbers_outgoing_callerid_single_user()
	{
		$group = 'GET numbers/outgoingcallerid';

		$callerids = $this->get_url(site_url('numbers/outgoingcallerid'),
									array());
		
		$callerids = json_decode($callerids);
		$this->run($group, 'callerid list received', true, is_array($callerids));
	}

	private function test_numbers_outgoing_callerid_multi_user()
	{
		$group = 'GET multi/numbers/outgoingcallerid';

		$callerids = $this->get_url(site_url('multi/numbers/outgoingcallerid'),
									array());
		
		$callerids = json_decode($callerids);
		$this->run($group, 'callerid list received', true, is_array($callerids));
	}

	private function test_numbers_token_single_user()
	{
		$group = 'GET numbers/token';

		$token = $this->get_url(site_url('numbers/token'),
									array());
		$token = json_decode($token);
		$this->run($group, 'callerid list received', true, is_object($token));
	}

	private function test_numbers_token_multi_user()
	{
		$group = 'GET multi/numbers/token';

		$token = $this->get_url(site_url('multi/numbers/token'),
									array());
		$token = json_decode($token);
		$this->run($group, 'callerid list received', true, is_object($token));
	}

	private function connect_url($url)
	{
		$ch = curl_init();
		$headers = array(
						 'Authorization: Basic '.$this->test_pass,
						 'Accept: application/json',
						 );
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		return $ch;
	}

	private function post_url($url, $fields)
	{
		$ch = $this->connect_url($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		
		$data = curl_exec($ch);
		
		curl_close($ch);
		
		return $data;
	}

	private function get_url($url, $fields)
	{
		$ch = $this->connect_url($url . '?' . http_build_query($fields));

		$data = curl_exec($ch);
		
		if(curl_errno($ch))
		{
			return curl_error($ch);
		}
		
		curl_close($ch);

		return $data;
	}
}