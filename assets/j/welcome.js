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

if (top != self) {
	top.location = self.location;
}

jQuery(function($) {
	$('a.help').live('click', function(e) {
		e.stopPropagation();
		e.preventDefault();
		alert('Not yet!')
	});
	
	$('#welcome-steps').Steps({
		// validate step before switching
		validateCallbacks : {
			next : function(stepId, step) {
				switch (stepId) {
					case 1:
						window.location = OpenVBX.connect_base_uri + OpenVBX.connect_sid;
						return false;
						break;
				}
				return true;
			},
			prev : function(stepId, step) { return true; },
			submit : function() {
				var _this = $(this);
				$.post(OpenVBX.home + '/welcome/finish',
					{},
					function(r) {
						if (r.error == false) {
							window.location = OpenVBX.home;
						}
						else {
							_this.Steps.triggerError(r.message);
						}
					},
					'json'
				);
				return false;
			}
		},
		// run each time a step loads
		stepLoadCallback : function(stepId, step) {
			var _this = $(this);
			switch (stepId) {
				case 2:
					_this.Steps.disablePrev(true);
					break;
			}
		}
	});
	
	if($('.error').text() != '') {
		setTimeout(function() {
				$('#welcome-steps').Steps.toggleError(true)
			}, 1000);
	}
});