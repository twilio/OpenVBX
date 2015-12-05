<?php
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
	
class UserGroupPickerWidget extends AppletUIWidget
{
	protected $template = 'UserGroupPicker';
	protected $name;
	protected $label;
	protected $value;
		
	public function __construct($name, $label, $value = null)
	{
		$this->name = $name;
		$this->label = $this->buildLabel($label, $value);
		$this->value = $value;
		$this->owner_type = $this->buildOwnerType($value);
		$this->owner_id = $this->buildOwnerId($value);
		
		parent::__construct($this->template);
	}

	/**
	 * @param $label
	 * @param $value VBX_User|VBX_Group
	 * @return string
	 */
	private function buildLabel($label, $value)
	{
		if(!empty($value))
		{
			if(get_class($value) == 'VBX_User')
			{
				return $value->full_name() . " (" . $value->email . ")";
			}
			else
			{
				return $value->name;
			}
		}
		
		return $label;
	}

	private function buildOwnerType($value)
	{
		$owner_type = '';
		if(!empty($value))
		{
			$owner_type = get_class($value);
			$owner_type = strtolower($owner_type);
			$owner_type = str_replace('vbx_', '', $owner_type);
		}

		return $owner_type;
	}

	private function buildOwnerId($value)
	{
		$owner_id = '';
		if(!empty($value))
		{ 
			$owner_id = $value->id;
		}

		return $owner_id;
	}
	
	public function render($data = array())
	{
		
		$defaults = array('name' => $this->name,
						  'label' => $this->label,
						  'owner_id' => $this->owner_id,
						  'owner_type' => $this->owner_type,
						  );

		$data = array_merge($defaults, $data);

		return parent::render($data);
	}
}