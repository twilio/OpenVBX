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

class SiteCachesException extends Exception {}

class Caches extends User_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->user = VBX_User::get($this->session->userdata('user_id'));
		if (!$this->user->is_admin)
		{
			throw new SiteCachesException('Action not allowed to non-administrators');
		}
	}
	
	public function index()
	{
		redirect('/settings/site#about');
	}
	
	public function flush()
	{
		flush_minify_caches();
		$this->cache->flush();
		$this->api_cache->flush();
		
		$this->session->set_flashdata('error', 'Caches Flushed');
		redirect('/settings/site#about');
	}
}	