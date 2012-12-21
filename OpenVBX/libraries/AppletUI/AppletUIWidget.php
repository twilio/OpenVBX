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
	
class AppletUIWidget
{
	protected $template = '';

	public function __construct($template = '')
	{
		$this->template = 'OpenVBX/libraries/AppletUI/templates/' . $template . 'Template.php';
	}

	public function render($data = array())
	{
		$template = $this->template;
		
		if(empty($template))
			return '';

		if(!is_file($template))
		{
			throw new AppletUIWidgetException("Template does not exist: $template");
		}

		$data = $this->escape($data);
		
		ob_start();
		extract($data);
		include($template);
		$view = ob_get_contents();
		ob_end_clean();

		return $view;
	}

	private function escape($data)
	{
		if(is_string($data))
		{
			return htmlspecialchars($data, ENT_COMPAT, 'UTF-8', false);
		}
		else if(is_array($data))
		{
			foreach($data as $key => $v)
			{
				$data[$key] = $this->escape($v);
			}
		}
		
		return $data;
	}
}

class AppletUIWidgetException extends Exception {}
