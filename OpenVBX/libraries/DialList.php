<?php
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 * 
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.
 *
 *  The Original Code is OpenVBX, released June 15, 2010.
 *
 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.
 *
 * Contributor(s):
 **/
	
class DialListException extends Exception {}

class DialList {
	protected $users;
	
	public function __construct($users) {
		$this->users = $users;
	}
	
	/**
	 * Get a DialList object try
	 * Pass in a VBX_User or VBX_Group object to begin
	 *
	 * @param object users_or_group
	 * @return object DialList
	 */
	public static function get($users_or_group) {
		$users = array();
		$class = 'DialList';
		
		switch(true) {
			case is_array($users_or_group):
				if (current($users_or_group) instanceof VBX_User) {
					// list of users, set as users list and continue
					$users = $users_or_group;
				}
				else {
					// list of user ids, populate list
					$users = VBX_User::get_users($user_ids);
				}
				break;
			case $users_or_group instanceof VBX_Group:
				if (!empty($users_or_group->users)) {
					foreach ($users_or_group->users as $user) {
						array_push($users, VBX_User::get($user->user_id));
					}
				}
				break;
			case $users_or_group instanceof VBX_User:
				$class = 'DialListUser';
				// individual user, add to list and continue
				array_push($users, $users_or_group);
				break;
		}
		return new $class($users);
	}
	
	/**
	 * Repopulate an object from a list of user_ids
	 * Use DialList::getState() to get the list of user ids
	 * 
	 * @param array $user_ids 
	 * @return object DialList
	 */
	public static function load($data) {
		$users = array();
		
		if (!empty($data['user_ids'])) {
			foreach ($data['user_ids'] as $user_id) {
				array_push($users, VBX_User::get($user_id));
			}
		}
		
		return new DialList($users);
	}
	
	/**
	 * Return the object state as a list of user ids
	 * Use DialList::load($user_ids); to repopulate an object from the list
	 * 
	 * @return array
	 */
	public function get_state() {
		$user_ids = array();
		if (!empty($this->users)) {
			foreach ($this->users as $user) {
				array_push($user_ids, $user->id);
			}
		}
		
		return array(
			'type' => get_class($this),
			'user_ids' => $user_ids
		);
	}
	
	/**
	 * Return the first user in the current list state
	 * User is removed from the list, reducing the length of the list by 1
	 *
	 * @return mixed VBX_User or NULL
	 */
	public function next() {
		return array_shift($this->users);
	}
}

/**
 * Variant of the DialList that iterates over a single VBX_User's devices
 *
 * @package default
 */
class DialListUser extends DialList {
	public static function load($data) {
		$devices = array();
		$user = VBX_User::get($data['user_id']);

		// load devices (handle device_id 0 as special condition as a Client reference)
		if (!empty($data['device_ids'])) {
			foreach ($data['device_ids'] as $device_id) {
				if ($device_id > 0) {
					array_push($devices, VBX_Device::get($device_id));
				}
				else {
					array_push($devices, new VBX_Device((object) array(
														'id' => 0,
														'name' => 'client',
														'value' => 'client:'.$user->id,
														'sms' => 0,
														'sequence' => -99,
														'is_active' => 1,
														'user_id' => $user->id
													)));
				}
			}
		} 
				
		// replace device list
		$user->devices = $devices;
		return new DialListUser(array($user));
	}
	
	public function get_state() {
		// return list of device IDs left in the user
		$device_ids = array();
		
		foreach (current($this->users)->devices as $device) {
			array_push($device_ids, $device->id);
		}
		
		return array(
			'type' => get_class($this),
			'device_ids' => $device_ids,
			'user_id' => current($this->users)->id
		);
	}
	
	public function next() {
		return array_shift(current($this->users)->devices);
	}
}

?>