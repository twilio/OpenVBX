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

(function() {
	var uh = (function (deps) {
		return function() {
			deps.$('#upgrade-steps').Steps({
				validateCallbacks : {
					prev : function(stepId, step) { return true; },
					next : function(stepId, step) { return true; },
					submit : function(stepId, step) {
						var _success = false,
							_this = deps.$(this);

						_this.Steps.setButtonLoading('submit', true);

						deps.$.ajax({
							url : deps.OpenVBX.home + '/upgrade/setup',
							data : {},
							type : 'post',
							async : false,
							dataType : 'json',
							success: function(r) {
								_success = r.success;
								if (!r.success) {
									_this.triggerError(r.message);
								}
							},
							error : function(XHR, textStatus, errorThrown) {
								_this.triggerError('An application error occurred.  Please try again.');
							}
						});

						_this.Steps.setButtonLoading('submit', false);
						return _success;
					}
				},
				stepLoadCallback : function(stepId, step) { return true; }
			});

			if(deps.$('.error').text() != '') {
				deps.setTimeout(function() {
						deps.$('#upgrade-steps').Steps.toggleError(true)
					}, 1000);
			}
		}
	});

	if (typeof(document) != "undefined")
		$(document).ready(uh({
			'$' : $,
			'setTimeout' : setTimeout,
			'OpenVBX' : OpenVBX
		}));
	else {
		upgrade_handler = uh;
	}
})();