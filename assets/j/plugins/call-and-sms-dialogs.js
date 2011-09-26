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
	console.log(params);
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
}
	
/////////////////////////////////////////////////////
// Call Dialog

$(function () {	
	if ((window.parent.Client) && 
			((window.parent.Client.disabled
				|| typeof window.parent.Twilio == 'undefined'
				|| window.parent.Twilio.Device.status() == 'offline') 
			&& (!window.parent.Client.phono 
				|| !window.parent.Client.phono.connected()))) {
		// Twilio Client is offline, probably due to an error, so lets
		// disable the use of Client to prevent unexpected behavior
		
		// disable calls using Twilio Client, replace selector with hidden element to pre-set the device type
		$('#vbx-context-menu .call-dialog select[name="device"]').closest('label')
			.replaceWith($('<input type="hidden" name="device" value="primary-device" />'));
		
		// neuter the online/offline button
		var _status = $('#vbx-client-status');
		if (_status.hasClass('online')) {
			_status.removeClass('online').addClass('offline');
			window.parent.Client.status.setWindowStatus(false);
		}
		_status.addClass('disabled');
	}
	
	// options
	var globalTwilioCallLock = false;
	var distance = 35;
	var time = 250;
	var hideDelay = 100;
	
	var hideDelayTimer = null;
	var dialog = $('.call-dialog').css('opacity', 0);
	$('form', dialog).live('submit', function(event) {
		event.preventDefault();
	});

	var hideDialog = function (event, link) {
		// reset the timer if we get fired again - avoids double animations
		if (hideDelayTimer)
			clearTimeout(hideDelayTimer);
		link.shown = false;
		$('.call-button').data('link', link);

		// store the timer so that it can be cleared in the mouseover if required
		hideDelayTimer = setTimeout(function () {
			hideDelayTimer = null;
			dialog.animate({
				top: '-=' + distance + 'px',
				opacity: 0
			}, time, 'swing', function () {
					// once the animate is complete, set the tracker variables
				globalTwilioCallLock = false;
				// hide the dialog entirely after the effect (opacity alone doesn't do the job)
				dialog.css('display', 'none');
			});
		}, hideDelay);

		$('.screen').hide();
		$('.invoke-call-button span').text('Call');
		$('.call-dialing').hide();
		
		currentDialogHideFunction = null;
		currentDialogType = null;
		
		return false;
	};

	$('.twilio-call').each(function () {
			
		// tracker
		var beingShown = false;
		var link = this;

		link.shown = false;
		
		var trigger = $(this);
		var displayDialog = function (event, link) {

			if (currentDialogType == 'sms') {
				currentDialogHideFunction();
			}
			
			// stops the hide event if we move from the trigger to the dialog element
			if (hideDelayTimer) clearTimeout(hideDelayTimer);
			// don't trigger the animation again if we're being shown, or already visible
			if (beingShown || link.shown) {
				return false;
			} 

			if(globalTwilioCallLock) {
				globalTwilioCallLock = false;
				var oldLink = $('.call-button').data('link');
				oldLink.shown = false;
				$('.call-dialog').hide();
			}

			var phone = '';
			if(link) {
				if($(link).text() != 'Call') {
					phone = $(link).text();
				} else {
					phone = '';
				}
				target = $(link).attr('href');
			}

			globalTwilioCallLock = beingShown = true;
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
			$('.call-button').data('link', link);
			$('input[name="to"]', dialog).val(phone);
			$('input[name="target"]', dialog).val(target);
			$('.screen').show();
			
			currentDialogType = 'call';
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

	var callNumber = function(event) {
		event.preventDefault();
		var device = $('select[name="device"]', dialog).val();
		if (device == 'client') {
			clientDialNumber();
		}
		else {
			deviceDialNumber(event, this);
		}
	};

	var clientDialNumber = function() {
		window.parent.Client.call({
			'to': $('#dial-number', dialog).val(),
			'callerid': $(':input[name="callerid"]', dialog).val(),
			'Digits': 1,
			'clientType': $(':input[name="callerid"] :selected').attr('rel'),
			'appAddress': $(':input[name="callerid"] :selected').attr('app')
		});
		$('.close', dialog).click();
	};
	
	var deviceDialNumber = function(event, clicked) {
		$('.invoke-call-button span').text('Calling...');
		$('.call-dialing').show();

		var link = $(clicked).data('link');
		$(this).prop('disabled', true);
		var button = $(clicked);
		$.ajax({
			url : OpenVBX.home + '/messages/call',
			data : $('form input, form select', dialog),
			dataType : 'json',
			type : 'POST',
			success : function(data) {
				button.prop('disabled', false);
				hideDialog(event, link);
				if(!data.error) {
					$.notify('You are now being connected to ' + $('input[name="to"]', dialog).val());
					return;
				}

				$('.error-dialog').dialog('option', 'buttons', { 
					"Ok": function() { 
						$(this).dialog("close"); 
					} 
				});

				$('.error-dialog .error-code').text('');
				$('.error-dialog .error-message')
					.text('Unable to complete call. Message from server: '
						  + data.message);
				
				$('.error-dialog').dialog('open');
			}
		});
	};

	$('.call-button', dialog).click(callNumber);

	$('.screen').live('click', function(event) {
		event.preventDefault();
		if(globalTwilioCallLock) {
			$('.close', dialog).click();
		}
	});
	
	$('#vbx-client-status .client-button').live('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var parent = $(this).closest('#vbx-client-status'),
			client_status = true,
			status = null;
		
		// if (window.parent.Client.disabled || window.parent.Twilio.Device.status() == 'offline' || (!window.parent.Client.phono || !window.parent.Client.phono.connected())) {
		if ((window.parent.Client) && 
				((window.parent.Client.disabled
					|| typeof window.parent.Twilio == 'undefined'
					|| window.parent.Twilio.Device.status() == 'offline') 
				&& (!window.parent.Client.phono 
					|| !window.parent.Client.phono.connected()))) {
			client_status = false;
		}
		
		if (client_status) {
			if (parent.hasClass('online')) {
				// go offline
				status = false;
				parent.removeClass('online');
			}
			else {
				// go online
				status = true;
				parent.addClass('online');
			}
			
			window.parent.Client.status.setWindowStatus(status);
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

$(function () {
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
				target = $(link).attr('href');
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
			.text(160 - length);
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
