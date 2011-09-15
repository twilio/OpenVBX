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

ep(__METHOD__.' user_id: '.$user_id);		

		if (!empty($user_id) && $user = $this->validate_returning_user($user_id)) 
		{
ep(__METHOD__.' user: '.$user->email);
			$tenant = $this->db->get_where('tenants', array('id' => $user->tenant_id))->result();

ep(__METHOD__, $tenant);

			if (!empty($tenant[0])) 
			{
				$this->setup_connect_tenant($this->input->get('AccountSid'), $user->tenant_id);
				return redirect($tenant[0]->url_prefix.'/welcome#step-2');
			}
		}
		
		$this->returning_user_fail();
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
ep(__METHOD__, 'userdata: ', $userdata);
		if (!empty($userdata[0])) {
			$user = new VBX_User($userdata[0]);
			$list = implode(',', array(
								   $user->id,
								   $user->password,
								   $user->tenant_id,
								   $user->is_admin,
							   ));
			$expected_signature = VBX_User::salt_encrypt($list);
			$actual_signature = $this->session->userdata('signature');
			
			if ($expected_signature == $actual_signature) 
			{
				return $user;
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
		#$this->session->sess_destroy();
		return redirect('auth/login');
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