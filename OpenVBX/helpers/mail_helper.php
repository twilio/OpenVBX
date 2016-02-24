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

/**
 * @param string $recipient
 * @param string $subject
 * @param string $template
 * @param array $maildata
 * @return bool
 */
function openvbx_mail($recipient, $subject, $template, $maildata = array())
{	
	$ci = &get_instance();
	
	$from_email = $ci->settings->get('from_email', $ci->tenant->id);

	if(empty($from_email))
	{
		$domain = $ci->config->item('server_name');
		$from_email = "$from <do-not-reply@$domain>";
	}

	$message = $ci->load->view('emails/'.$template, $maildata, true);
	
	$ci->load->library('email');
	$ci->email->from($from_email);
	$ci->email->reply_to($from_email);
	$ci->email->to($recipient);
	$ci->email->subject('[OpenVBX] '.$subject);
	$ci->email->message($message);

	log_message('debug', 'MAILING -- to: '.$recipient.' -- body: '.$message);

	return $ci->email->send();
}