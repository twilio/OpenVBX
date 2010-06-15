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

$.notify = function (message, learn) {
	var close = '<a href="" class="close action"><span class="replace">Close</span></a>';
	var info = '';
	if(learn) {
		var info = ' <a href="'+learn+'">Learn more</a>';
	}
	$('.notify').addClass('hide');
	setTimeout(function() {
		$('.notify .message').text(message).append(info).append(close);
		$('.notify').removeClass('hide').fadeIn('slow');
		$('.notify').delay(10000).fadeOut('slow'); 
	}, 500);
	return $('.notify');
};

$(document).ready(function() {
	$('.notify .message .close.action').live('click', function(event) {
		event.preventDefault();
		$('.notify').dequeue().fadeOut('fast');
	});
});


