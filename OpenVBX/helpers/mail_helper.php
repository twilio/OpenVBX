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
	
function openvbx_mail($recipient, $subject, $template, $maildata = array())
{
	error_log('mailing');
	$path = APPPATH . 'views/emails/' . $template . '.php';
	$ci = &get_instance();
	$domain = $_SERVER['HTTP_HOST'];
	$from_email = $ci->settings->get('from_email', $ci->tenant->id);
	if(empty($from_email))
	{
		$from_email = "$from <do-not-reply@$domain>";
	}
	
	$headers = "From: $from_email";

	/* Render the mail template */
	ob_start();
	extract($maildata);
	include($path);
	$message = ob_get_contents();
	ob_end_clean();

	if($ci->config->item('log_threshold') > 2)
	{
		error_log($message);
	}

	return mail($recipient, "[OpenVBX] " . $subject, $message, $headers);
}