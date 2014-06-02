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

// We keep track of which dialog is open and how to close it so that,
// if the sms dialog is open and someone clicks the call button, the
// sms dialog hides and call shows.
var currentDialogHideFunction = null;
var currentDialogType = null;
	
/////////////////////////////////////////////////////
// Client Accessors

/**
 * Client Dial
 *
 * @example: 
 * 		clientDial({
 * 			'to': '+14158675309',
 *			'callerid': '+1415853-5937'
 * 		});
 *
 * @params object
 * @return void 
 */
OpenVBX.clientDial = function(params) {
	params = $.extend(params, { 'Digits': 1 });
	window.parent.Client.call(params);
};

/**
 * Client Hangup
 * 
 * @return void
 */
OpenVBX.clientHangup = function() {
	window.parent.Client.hangup();
};

/**
 * Client Mute
 *
 * @return void
 */
OpenVBX.clientMute = function() {
	window.parent.Client.mute();
};

/**
 * Client Unmute
 *
 * @return void
 */
OpenVBX.clientUnMute = function() {
	window.parent.Client.unmute();
};

/**
 * See if the browser client is in a ready state
 *
 * @return bool
 */
OpenVBX.clientIsReady = function() {
	return window.parent.Client.isReady();
};

/**
 * Presence notifier
 * Monitor this object with $(OpenVBX.presence).bind('presence', function(client, onlineClients) { ... });
 */
OpenVBX.presence = {
	onlineClients : [],
	_set : function(client, onlineClients) {
		this.onlineClients = onlineClients;
		$(this).trigger('presence', [client, OpenVBX.presence.onlineClients]);
	}
};
if (window.parent.Client) {
	OpenVBX.presence.onlineClients = window.parent.Client.clients;
}
	
/////////////////////////////////////////////////////
// Call Dialog

jQuery(function($) {	
	if (window.parent.Client && !window.parent.Client.isReady()) {
		var _status = $('#vbx-client-status'),
			_userstatus = null;
		
		_userstatus = (_status.hasClass('online') ? 'online' : 'offline');
		_status.removeClass('online').addClass('loading');
		
		window.parent.Client.onready = function() {
			_status.removeClass('loading');
			if (_userstatus == 'online') {
				_status.addClass('online');
			}
			return;
		};		
	}
	
	// @todo pull out when html is deleted from view
	var dialog = $('.call-dialog').css('opacity', 0);

	$('.twilio-call').live('click', function(e) {
		e.preventDefault();

		switch (window.parent.Client.ui.state()) {
			case 'closed':
				window.parent.Client.ui.toggleCallView('open');
				break;
			case 'open':
				if (window.parent.Client.status.getCallStatus()) {
					window.parent.Client.ui.toggleTab();
				}
				else {
					window.parent.Client.ui.toggleCallView('close');
				}
				break;
			case 'tab':
				window.parent.Client.ui.toggleTab();
				break;
			default:
				// do nothing
		}
	});

	$('#vbx-client-status .client-button').live('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
	
		var parent = $(this).closest('#vbx-client-status'),
			client_status = true,
			status = null;
	
		if (window.parent.Client.disabled) {
			client_status = false;
		}
	
		if (client_status) {
			status = parent.hasClass('online') ? false : true;
			parent.removeClass('online').addClass('loading');
		
			window.parent.Client.status.setWindowStatus(status, function(r) {
				parent.removeClass('loading').addClass(r.client_status);
			});
		}
		else {
			$('.error-dialog .error-code').text('');
			$('.error-dialog .error-message')
				.text('The Phone Client is not available. ' +
					  'Please check to make sure that you have Flash installed ' +
					  'and that there are no Flash Blocking plugins enabled.');
		
			$('.error-dialog').dialog('open');
		}
	});
});

/////////////////////////////////////////////////////
// SMS Dialog

jQuery(function($) {
	// options
	var globalTwilioSmsLock = false;
	var distance = 35;
	var time = 250;
	var hideDelay = 100;
	
	var hideDelayTimer = null;
	var dialog = $('.sms-dialog').css('opacity', 0);
	$('form', dialog).live('submit', function(event) {
		event.preventDefault();
	});

	var hideDialog = function (event, link) {
		$('.send-sms-button').prop('disabled', false);

		// reset the timer if we get fired again - avoids double animations
		if (hideDelayTimer)
			clearTimeout(hideDelayTimer);
		if(!link) {
			link = $('.sms-button').data('link');
		}

		link.shown = false;
		$('.sms-button').data('link', link);

		// store the timer so that it can be cleared in the mouseover if required
		hideDelayTimer = setTimeout(function () {
			hideDelayTimer = null;
			dialog.animate({
				top: '-=' + distance + 'px',
				opacity: 0
			}, time, 'swing', function () {
				// once the animate is complete, set the tracker variables
				globalTwilioSmsLock = false;
				// hide the dialog entirely after the effect (opacity alone doesn't do the job)
				dialog.css('display', 'none');
			});
		}, hideDelay);

		$('.screen').hide();
		$('.send-sms-button span').text('Send SMS');
		$('.sms-sending').hide();
		
		currentDialogHideFunction = null;
		currentDialogType = null;

		return false;
	};
	

	$('.twilio-sms').each(function () {

		// tracker
		var beingShown = false;
		var link = this;

		link.shown = false;
		
		var trigger = $(this);
		var displayDialog = function (event, link) {
			$('.send-sms-button').attr('rel', $(link).attr('rel'));
			$('.send-sms-button').prop('disabled', false);
			
			if (currentDialogType == 'call') {
				currentDialogHideFunction();
			}

			// stops the hide event if we move from the trigger to the dialog element
			if (hideDelayTimer) clearTimeout(hideDelayTimer);
			// don't trigger the animation again if we're being shown, or already visible
			if (beingShown || link.shown) {
				return false;
			} 

			if(globalTwilioSmsLock) {
				globalTwilioSmsLock = false;
				var oldLink = $('.sms-button').data('link');
				oldLink.shown = false;
				$('.sms-dialog').hide();
			}

			var phone = '';
			if(link) {
				if(!$(link).text().match(/sms/i)) {
					phone = $(link).text();
				} else {
					phone = '';
				}
				target = $(link).attr('data-href');
			}

			globalTwilioSmsLock = beingShown = true;
			// reset position of dialog box
			dialog.css({
				position: 'absolute',
				left: trigger.get(0).offsetLeft,
				top: trigger.get(0).offsetTop,
				display: 'block' // brings the dialog back in to view
			})
			// (we're using chaining on the dialog) now animate it's opacity and position
				.animate({
					top: '+=' + distance + 'px',
					opacity: 1
				}, time, 'swing', function() {
					// once the animation is complete, set the tracker variables
					beingShown = false;
					link.shown = true;
				});
			$('.sms-button').data('link', link);
			$('input[name="to"]', dialog).val(phone);
			$('input[name="target"]', dialog).val(target);
			$('.screen').show();
			dialog.find('input.small').focus();
			
			currentDialogType = 'sms';
			currentDialogHideFunction = function() {
				hideDialog(null, link);
			};
			
			return true;
		};

		$(window).keypress(function(event) {
			if(event.keyCode == 27)
				hideDialog(event, link);
		});

		if($(this).hasClass('hover'))
		{
			// set the mouseover and mouseout on both element
			$([trigger.get(0), dialog.get(0)])
				.mouseover(function(event) {
					displayDialog(event, link);
				})
				.mouseout(function(event) {
					hideDialog(event, link);
				});
		} else {
			$(trigger).click(function(event) { 
				return (displayDialog(event, link) ? false : hideDialog(event, link));
			});
		}

		$('.close', dialog).live('click', function(event) {
			hideDialog(event, link);
			event.preventDefault();
		});
		
	});


	var smsNumber = function(event) {
		$('.send-sms-button span').text('Sending...');
		$('.send-sms-button .sms-sending').show();
		var link = $(this).data('link');
		$(this).prop('disabled', false);
		var message_id = $(event.target).attr('rel');
		var button = $(this);
		$.ajax({
			url : OpenVBX.home + '/messages/sms' + (message_id? '/'+ message_id : ''),
			data : $('form input, form select, form textarea', dialog),
			dataType : 'json',
			type : 'POST',
			success : function(data) {
				button.prop('disabled', false);
				hideDialog(event, link);
				if(!data.error) {
					$.notify("SMS sent to "+ $('input[name="to"]', dialog).val());
					$('textarea', dialog).val('');
					$('input', dialog).val('');
					return;
				}

				$('.error-dialog').dialog('option', 'buttons', { 
					"Ok": function() { 
						$(this).dialog("close"); 
					} 
				});

				$('.error-dialog .error-code').text('');
				$('.error-dialog .error-message')
					.text('Unable to send sms. Message from server: '
						  + data.message);
				
				$('.error-dialog').dialog('open');
			}
		});
		
		return false;
	};

	$('.send-sms-button', dialog).click(smsNumber);
	var updateCount = function() {
		var length = $(this).val().length;
		$('.count', dialog)
			.text(1600 - length);
	};

	$('textarea', dialog).live('keypress', updateCount);
	$('textarea', dialog).live('keyup', updateCount);
	$('textarea', dialog).live('load', updateCount);
	$('.screen').live('click', function(event) {
		event.preventDefault();
		if(globalTwilioSmsLock) {
			$('.close', dialog).click();
		}
	});
});
