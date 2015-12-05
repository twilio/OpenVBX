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
	
class VBX_ThemeException extends Exception {}

/**
 * Class VBX_Theme
 * @property CI_Loader $load
 */
class VBX_Theme extends Model
{
	public function __construct()
	{
		parent::Model();
		$this->load->helper('file');
		$this->load->helper('directory');
	}

	public function is_valid($name)
	{
		$name = preg_replace('/[^0-9a-zA-Z-_]/', '', $name);
		$themes = directory_map('assets/themes', true);
		if(in_array($name, $themes))
		{
			return true;
		}

		return false;
	}

	public function get_all()
	{
		$themes = directory_map('assets/themes', true);
		
		return $themes;
	}

	public function get_iphone_json($name)
	{
		$name = preg_replace('/[^0-9a-zA-Z-_]/', '', $name);
		return read_file('assets/themes/'.$name.'/iphone.json');
	}
}