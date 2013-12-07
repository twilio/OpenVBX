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
	
class MY_URI extends CI_URI {

	function uri_string()
	{
		$uri = parent::uri_string();
		$ci = &get_instance();
		if($ci->tenant && $ci->tenant->id > 1)
		{
			$uri = preg_replace('/^\/'.$ci->tenant->url_prefix.'/i', '', $uri);
		}
		return $uri;
	}

	function _explode_segments()
	{
		foreach(explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $this->uri_string)) as $val)
		{
			// Filter segments for security
			$val = trim($this->_filter_uri($val));
			$val = preg_replace('/\?.*$/','', $val);
			if ($val != '')
			{
				$this->segments[] = $val;
			}
		}
	}
}
