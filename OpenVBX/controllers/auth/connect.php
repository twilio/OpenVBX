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

class Connect extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->section = 'connect';
		
		// no cache
		$ci =& get_instance();
		$ci->cache->enabled(false);
	}
	
	/**
	 * Connect authorizations need a hard url to return to, which means that tenants
	 * must return to the parent install. Do our best here to validate that the user
	 * is who he/she says they are.
	 *
	 * @return void
	 */
	public function index() 
	{		
		$user_id = $this->session->userdata('user_id');

		if (!empty($user_id) && $user = $this->validate_returning_user($user_id)) 
		{
			$tenant = $this->db->get_where('tenants', array('id' => $user->tenant_id))->result();

			if (!empty($tenant[0])) 
			{
				if ($account_sid = $this->input->get('AccountSid')) // regular account sid
				{
					$this->setup_connect_tenant($account_sid, $user->tenant_id);
					redirect($tenant[0]->url_prefix.'/welcome#step-2');
				}
				elseif ($error = $this->input->get('error')) // unauthorized_client
				{
					$this->setup_connect_tenant($error, $user->tenant_id);
					redirect($tenant[0]->url_prefix.'/welcome');
				}
			}
		}
		else
		{
			log_message('error', 'Could not validate returning user: '.$user_id);
		}

		$this->returning_user_fail();
	}
	
	/**
	 * Twilio calls us when a user deauthorizes our connect account
	 *
	 * @return void
	 */
	public function deauthorize() 
	{
		$header = '405'; // method not allowed

		if (OpenVBX::validateRequest() || 1 == 1) // currently bypassing authorization until signatures are correct
		{ 
			if ($account_sid = $this->input->post('AccountSid')) 
			{
				$result = $this->db->select('tenants.*')
									->from('tenants')
									->join('settings', 'tenants.id = settings.tenant_id')
									->where('settings.name = "twilio_sid"')
									->where('settings.value = "'.$this->db->escape_str($account_sid).'"')
									->limit(1)
									->get()->result();
			
				if (!empty($result)) 
				{
					$tenant = current($result);
					if ($tenant->id) {
						log_message('info', 'AccountSid "'.$account_sid.'" deauthorized on '.date('r').' (server tz: '.date_default_timezone_get().')');
						$this->setup_connect_tenant('deauthorized_client', $tenant->id);
						$header = '204'; // accepted, but no content to return
					}
				}
				
				$header = '400'; // sid not found so its a bad request
			}
		}
		
		set_status_header($header);
		exit;
	}
	
	public function account_deauthorized() 
	{
		// the account that the user has tried to access has been deauthorized by someone	
		$data = array();
		$this->respond('Account Deauthorized', 'account-deauthorized', $data, 'login-wrapper', 'layout/login');
	}
	
	/**
	 * Validate the user's signature & state
	 * State is generated in the welcome controller and passed through the oauth process
	 * 
	 * @todo process $state passed back through the oauth process
	 *
	 * @param int $user_id 
	 * @return mixed VBX_User or false
	 */
	protected function validate_returning_user($user_id) 
	{
		// jump through hoops to get around the Tenantization
		$userdata = $this->db->get_where('users', array('id' => $user_id))->result();

		if (!empty($userdata[0])) 
		{
			$actual_signature = $this->session->userdata('signature');
			if (VBX_User::check_signature($userdata[0], $actual_signature)) 
			{
				return $userdata[0];
			}
			else
			{
				return false;
			}
		}
	}
	
	/**
	 * Blindly redirect and nuke the session on validation failure
	 *
	 * @return void
	 */
	protected function returning_user_fail() 
	{
		$this->session->sess_destroy();
		redirect('auth/login');
	}

	/**
	 * Since we're not in the context of a tenant and to avoid jumping through
	 * hoops to get a proper account REST API object we only log the Account Sid.
	 * Final Client application setup is in the final stage of the welcome steps.
	 *
	 * @param string $account_sid 
	 * @param int $tenant_id
	 * @return mixed Int or false
	 */
	protected function setup_connect_tenant($account_sid, $tenant_id) {
		return $this->vbx_settings->add('twilio_sid', $account_sid, $tenant_id);
	}
}