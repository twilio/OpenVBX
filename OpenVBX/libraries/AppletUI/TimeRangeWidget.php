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

class TimeRangeWidget extends AppletUIWidget
{
	protected $template = 'TimeRange';
	
	public function __construct($name, $from, $to)
	{
		$this->name = $name;
		$this->from = $from;
		$this->to = $to;
		parent::__construct($this->template);
	}

	public function render($data = array())
	{
		$defaults = array(
			'name' => $this->name,
			'from' => $this->from,
			'to' => $this->to,
		);

		$data = array_merge($defaults, $data);
		return parent::render($data);
	}
}
