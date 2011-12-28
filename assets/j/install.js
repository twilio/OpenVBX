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

$(document).ready(function() {
	$('#install-steps').Steps({
		validateCallbacks : {
			next : function (stepId, step) {
				var _this = this,
					_success = false,
					params = $('textarea, input, select', step);
				
				_this.Steps.setButtonLoading('next', true);
			
				$.ajax({
					url : OpenVBX.home + '/install/validate',
					data : params,
					type : 'post',
					dataType : 'json',
					async : false,
					success : function(r) {
						$('.invalid', step).removeClass('invalid');
						_success = r.success;
						if (!r.success) {
							for (var a in r.errors) {
								$('#' + a + '-' + stepId).addClass('invalid');
							}
							_this.Steps.triggerError(r.message);
						}
						else {
							_this.Steps.clearError();
						}
					},
					error : function(XHR, textStatus, errorThrown) {
						_this.Steps.triggerError('An application error occurred.  Please try again.');
					}
				});
			
				_this.Steps.setButtonLoading('next', false);
				return _success;
			},
			prev : function (stepId, step) {
				return true;
			},
			submit : function (stepId, step) {
				var _this = $(this),
					_success = false;

				_this.Steps.setButtonLoading('submit', true);
				_this.Steps.setButtonLoading('next', true);
				
				$.ajax({
					url : OpenVBX.home + '/install/validate',
					data : $('textarea, input, select', step),
					type : 'post',
					dataType : 'json',
					async : false,
					success : function (r) {
						$('.invalid', step).removeClass('invalid');
						_success = r.success;
						if (!r.success) {
							for (var a in r.errors) {
								$('#' + a + '-' + stepId).addClass('invalid');
							}
							_this.Steps.triggerError(r.message);
						}
						else {
							_this.Steps.clearError();
							_success = doInstall(_this);
						}
					},
					error : function(XHR, textStatus, errorThrown) {
						_this.Steps.triggerError('An application error occurred.  Please try again.');
					}
				});
								
				_this.Steps.setButtonLoading('submit', false);
				_this.Steps.setButtonLoading('next', false);
				return _success;
			}
		}
	});

	var doInstall = function(_this) {
		var _installSuccess = false;
		
		$.ajax({
			url : OpenVBX.home + '/install/setup',
			data : $('form input, form select, form textarea'),
			type : 'post',
			async : false,
			dataType : 'json',
			success : function(r) {
				_installSuccess = r.success;
				if (!r.success) {
					_this.Steps.triggerError(r.error);
				}
			},
			error : function(XHR, textStatus, errorThrown) {
				_this.Steps.triggerError('An application error occurred.  Please try again.');
			}
		});
		return _installSuccess;
	}
	
	if($('.error').text() != '') {
		setTimeout(function() {
				$('#install-steps').Steps.toggleError(true)
			}, 1000);
	}
	
	setTimeout(function() {
		$.ajax({ 
			url : OpenVBX.home.replace('index.php', 'support/rewrite'), 
			success : function(data, code) {
				$('input[name="rewrite_enabled"]').attr("value", 1);
			}, 
			error : function(data) { 
				$('input[name="rewrite_enabled"]').attr("value", 0);
			} 
		});
	}, 1000);
});