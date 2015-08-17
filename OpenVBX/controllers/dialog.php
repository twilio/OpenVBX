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

/**
 * @param VBX_User|stdClass $a
 * @param VBX_User|stdClass $b
 * @return int
 */
function sortUsersAndGroupsByNameComparator($a, $b)
{
	$aName = null;
	$bName = null;
	
	if ($a instanceof VBX_User)
	{
		$aName = $a->first_name . ' ' . $a->last_name;
	}
	else
	{
		$aName = $a->name;
	}
	
	if ($b instanceof VBX_User)
	{
		$bName = $b->first_name . ' ' . $b->last_name;
	}
	else
	{
		$bName = $b->name;
	}

	return strcasecmp($aName, $bName);
}

class Dialog extends User_Controller {

	function __construct()
	{
		parent::__construct();
		$this->admin_only('dialogs');
		$this->template->set_template('dialog');
	}
	
	function usergroup()
	{
		$data = array();
		$users = VBX_User::search(array('is_active' => 1));
		$groups = VBX_Group::search(array('is_active' => 1));
		
		$data['users_and_groups'] = array_merge($users, $groups);
		
		usort($data['users_and_groups'], "sortUsersAndGroupsByNameComparator");
		
		$this->respond('', 'dialog/usergroup', $data);
	}
}