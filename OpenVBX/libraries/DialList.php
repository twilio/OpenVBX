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

class DialList implements Countable 
{
	protected $users;
	
	public function __construct($users = array()) 
	{
		// clone users in to the object since we're gonna
		// mess with their device lists
		if (!empty($users))
		{
			foreach ($users as $user)
			{
				$this->users[] = clone $user;
			}
		}
	}
	
	public function count() 
	{
		return count($this->users);
	}
	
	/**
	 * Get a DialList object try
	 * Pass in a VBX_User or VBX_Group object to begin
	 *
	 * @param VBX_User|VBX_Group $users_or_group
	 * @return DialList
	 */
	public static function get($users_or_group)
	{
		$users = array();
		$class = 'DialList';
		
		switch(true) 
		{
			case is_array($users_or_group):
				if (current($users_or_group) instanceof VBX_User) 
				{
					// list of users, set as users list and continue
					$users = $users_or_group;
				}
				else 
				{
					// list of user ids, populate list
					$users = VBX_User::get_users($users_or_group);
				}
				break;
			case $users_or_group instanceof VBX_Group:
				if (!empty($users_or_group->users)) 
				{
					foreach ($users_or_group->users as $user) 
					{
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
	 * Return the object state as a list of user ids
	 * Use DialList::load($user_ids); to repopulate an object from the list
	 * 
	 * @return array
	 */
	public function get_state() 
	{
		$user_ids = array();
		if (count($this->users)) 
		{
			foreach ($this->users as $user) 
			{
				array_push($user_ids, $user->id);
			}
		}
		
		return array(
			'type' => get_class($this),
			'user_ids' => $user_ids
		);
	}
	
	/**
	 * Return the first user's primary device in the current list state
	 * User is removed from the list, reducing the length of the list by 1
	 *
	 * @return mixed VBX_Device or NULL
	 */
	public function next() 
	{
		$device = null;
		
		while($device == null && count($this->users))
		{
			$user = array_shift($this->users);
			if (count($user->devices)) 
			{
				foreach ($user->devices as $user_device) 
				{
					if ($user_device->is_active) 
					{
						$device = $user_device;
						break;
					}
				}
			}
		}
		
		return $device;
	}
}

/**
 * Variant of the DialList that iterates over a single VBX_User's devices
 *
 * @package default
 */
class DialListUser extends DialList 
{
	
	public function count() 
	{
		return count($this->users[0]->devices);
	}
	
	public function get_state() 
	{
		// return list of device IDs left in the user
		$device_ids = array();
		
		if (count($this->users[0]->devices)) 
		{
			foreach ($this->users[0]->devices as $device) 
			{
				array_push($device_ids, $device->id);
			}
		}
		
		return array(
			'type' => get_class($this),
			'device_ids' => $device_ids,
			'user_id' => $this->users[0]->id
		);
	}
	
	public function next() 
	{
		$device = null;
		
		while($device == null && count($this->users[0]->devices)) 
		{
			$user_device = array_shift($this->users[0]->devices);
			if ($user_device->is_active) 
			{
				$device = $user_device;
			}
		}

		return $device;
	}
}