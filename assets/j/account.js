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
	// $.scrollTo('0px');

	/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
	$('.voicevault-setup').click(function() {
		$.getJSON(OpenVBX.home + '/voicevault/web/setup',
			function(data) {
				alert(data.message);
			}
		);
		return false;
	});
	/** End Disruptive Technologies code **/

	$("#account-edit").validate({
		rules: {
			first_name: {
				required: true,
				minlength: 2
			},
			last_name: {
				required: true,
				minlength: 2
			},
			email: {
				required: true,
				email: true
			},
			pin: {
				digits: true,
				minlength: 2
			},
			pin2: {
				equalTo: '#iPin'
			}
		},
		messages: {
			first_name: {
				required: "First name required",
				minlength: "First name too short"
			},
			last_name: {
				required: "Last name required",
				minlength: "Last name too short"
			},
			email: {
				required: "Email required",
				email: "Must be a valid email address"
			},
			pin: {
				digits: "Numbers only",
				minlength: "PIN too short"
			},
			pin2: {
				equalTo: "Must match PIN"
			}
		}
	});

	valPassword = $("#account-password").validate({
		rules: {
			old_pw: {
				required: true,
				minlength: 2
			},
			new_pw1: {
				required: true,
				minlength: 2
			},
			new_pw2: {
				required: true,
				equalTo: '#iNewPw1'
			}
		},
		messages: {
			old_pw: {
				required: "Old password required",
				minlength: "Password too short"
			},
			new_pw1: {
				required: "New password required",
				minlength: "Password too short"
			},
			new_pw2: {
				required: "Must confirm new password",
				equalTo: "Must match new password"
			}
		}
	});

	/* VBX Content Tabs */
	$('.vbx-content-tabs').each(function() {
		var tabs = this;
		$('li', this).click(function(e) {
			$('li', tabs).removeClass('selected');
			$(this).addClass('selected');
			var anchor = $('a', this).attr('href').replace(/^.*#/, '');
			$('.vbx-tab-view').hide();
			$('#account-'+anchor).show();

			document.location.href = document.location.href.replace(/^.*/,'#'+anchor);
			return true;
		});

		var hash = function() {
			var _hash =  document.location.hash.replace('#','');
			if(_hash == '') {
				_hash = "devices";
			}
			return _hash;
		};

		$(window).hashchange( function() { $('a[href="#'+hash()+'"]').click(); } );
		$(window).trigger( "hashchange" );
		$('a[href="#'+hash()+'"]').click();
		history.navigationMode = 'compatible';

	});

	/* Audio Modal Tabs */
	$('#audio-modal').modalTabs({ history : false });

	/* Change Password */
	var changePassword = function() {
		$('button').prop('disabled', true);
		var passwordChanged = function(data, status) {
			$('#dialog-password .error-message').slideUp(function() {
				$('#dialog-password .error-message').text(data.message);
				$('#dialog-password .error-message').slideDown();
			});
			
			if(data.error)
			{
				$('button').prop('disabled', false);
				return false;
			}
			
			setTimeout(function() {
				$('#dialog-password input').val('');
				$('button').prop('disabled', false);
				$('#dialog-password .error-message').hide();				
				return $('#dialog-password').dialog('close');
			}, 1000);			
		};
		var passwordChangeFailed = function(xhr, status, error) {};
		$.ajax({
			type: 'POST',
			url:  OpenVBX.home + '/account/password',
			data: $('#dialog-password input'),
			success: passwordChanged,
			error: passwordChangeFailed,
			dataType: 'json'
		});
	};
	
	$('#dialog-password').dialog({ 
		autoOpen: false,
		width: 350,
		buttons: {
			'Change' : changePassword,
			'Cancel' : function() {
				$('.error-message', this).hide();
				$(this).dialog('close');
			}
		}
	});
	
	// If the user presses enter while in the form, we should act as if they pressed the change
	// password button.
	$('#dialog-password').submit(function(){
		changePassword();
		return false;
	})

	var openPasswordDialog = function() {
		$('#dialog-password').dialog('open');
	};

	$('.change-password').click(openPasswordDialog);


	$('.voicemail-container').find('.audio-choice').bind('save', function(event, mode, value){
		if (value) {
			$.ajax({
				type: 'POST',
				url:  OpenVBX.home + '/voicemail/greeting',
				data: { 'voicemail' : value },
				success: function() {
				},
				error: function() {
				},
				dataType: 'json'
			});
		}
	});	
});
