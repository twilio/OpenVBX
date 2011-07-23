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
// Client

var Client = {
	connection: false,
	
	onready: null,
		
	options: {
		cookie_name: 'vbx_client_call',
		check_timeout: 5000 // how often the parent window should check client window status
	},
	
	message: function (status) {
		console.log(status);
		$('#client-ui-message').text(status);
	},
	
	init: function () {
		// capture "button" clicks
		$('.client-ui-button').live('click', function(event) {
			event.stopPropagation();
			var key = $(this).children('.client-ui-button-number').text();
			Client.ui.pressKey(key);
		});
		
		// treat cookie as source of truth
		var cookieval = this.status.getCookie();
		if (typeof cookieval == 'object') {
			if (cookieval.window_open) {
				this.status.window_open = cookieval.window_open;
			}
			if (cookieval.on_call) {
				this.status.on_call = cookieval.on_call;
			}
		}
	}, 
	
	translateKeyCode: function (code) {
		var number = code - 48;
		
		switch(number) {
			case -13:
				return '#'
			case -6:
				return '*';
			case 0:
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
			case 9:
				return number.toString(10);
		}
		return null;
	},
	
	call: function (params) {
		console.log(params);
		this.connection = Twilio.Device.connect(params);
	},

	hangup: function () {
		if (this.connection) {
			this.connection.disconnect();
			this.connection = false;
		}
	},
	
	close: function() {
		window.close();
	},

// listeners

	incoming: function (connection) {
		this.message('Incoming call from: ' + connection.parameters.From);
		if (!this.connection) {
			this.connection = connection;
			// notify user of incoming call in future versions	
		}
	},

	accept: function (connection) {
		this.connection.accept();
	}, 

	error: function (error) {
		this.ui.endTick();
		this.status.setCallStatus(false);
		this.message(error);
	},

	connect: function (conn) {
		this.ui.startTick();
		this.ui.show('hangup');
		this.status.setCallStatus(true);
		this.message('Calling');
	},

	disconnect: function (conn) {
		this.ui.endTick();
		this.ui.hide('hangup');
		this.status.setCallStatus(false);
		this.message('Call ended');
	},

	offline: function (device) {
		this.status.setCallStatus(false);
		this.message("Offline");
	},

	ready: function (device) {
		this.message('Ready');
		$('#client-ui-dial').show();	
		if (typeof this.onready == 'function') {
			this.onready.call();
		}
	}
};

Client.ui = {
// Buttons	
	pressKey: function(key) {
		$('#client-ui-number').focus().val($('#client-ui-number').val() + key);
		if(!Client.connection || !key) {
			return;
		}
		Client.connection.sendDigits(key);
	},
	
	show: function(element) {
		$('#client-ui-' + element).show();
	},
	
	hide: function(element) {
		$('#client-ui-' + element).hide();
	},
	
// Timer
	startTick: function() {
		this.startTime = new Date();
		this.tickInterval = setInterval('Client.ui.tick()', 1000);
		this.displayTime();
	},

	tick: function() {
		this.displayTime();
	},

	endTick: function() {
		this.startTime = null;
		if(this.tickInterval) {
			clearInterval(this.tickInterval);
			this.tickInterval = null;
		}
	},

	getTicks: function() {
		var currentTime = new Date();
		return currentTime.getTime() - this.startTime.getTime();
	},

	displayTime: function() {
		var seconds = Math.floor(this.getTicks() / 1000);

		var minutes = Math.floor(seconds / 60);
		var seconds = seconds % 60;

		if(minutes < 10) {
			minutes = '0' + minutes;
		}

		if(seconds < 10) {
			seconds = '0' + seconds;
		}

		$('#client-ui-timer').text(minutes + ':' + seconds);
	},
	
// window pop
	
	openWindow: function(params) {
		var paramstring = '';
		if (params) {
			paramstring = '?' + $.param(params);
		}
		var window_url = OpenVBX.home + '/messages/client' + paramstring;
		var window_opts = 'location=0,status=0,width=600,height=350,scrollbars=0,menubar=0,resizable=0';
		
		Client.status.setWindowStatus(true);
		return window.open(window_url, 'client_caller', window_opts);
	}
};

Client.status = {	
	setCallStatus: function (status) {
		this.setCookieVal('on_call', status);
	},
	
	getCallStatus: function () {
		this.getCookieVal(on_call);
	},
	
	setWindowStatus: function (status) {
		this.setCookieVal('window_open', status);
	},
	
	getWindowStatus: function () {
		return this.getCookieVal('window_open');
	},
	
	setCookieVal: function (key, val) {
		var cookie_val = this.getCookie();
		// set cookie to relevant window status 
		cookie_val[key] = val;
		$.cookie(Client.options.cookie_name, JSON.stringify(cookie_val), {path: '/'});
	},
	
	getCookieVal: function(key) {
		var cookie_val = this.getCookie();
		return cookie_val[key];
	},
	
	getCookie: function () {
		var cookie_val = $.cookie(Client.options.cookie_name);
		if (cookie_val == null) {
			cookie_val = {};
		}
		else {
			cookie_val = JSON.parse(cookie_val);
		}
		return cookie_val;
	},
	
	displayOnlineStatus: function() {
		clearTimeout(this.client_timeout_check);
		if (this.getWindowStatus()) {
			$('#vbx-client-status').addClass('online').find('span.client-status').text('Online').show();
		}
		else {
			$('#vbx-client-status').removeClass('online').find('span.client-status').text('Offline').show();
		}
		this.client_timeout_check = setTimeout('Client.status.displayOnlineStatus()', Client.options.check_timeout);
	}
};

var clientCall = function (params) {
	Client.call(params);
};

var clientAccept = function () {
	Client.accept();
};

var clientHangup = function () {
	Client.hangup();
};

var clientPromptAnswer = function () {
	Client.incoming();
};

Twilio.Device.setup(OpenVBX.client_capability);

Twilio.Device.ready(function (device) {
	Client.ready(device);
});

Twilio.Device.offline(function (device) {
	Client.offline(device);
});

Twilio.Device.error(function (error) {
	Client.error(error);
});

Twilio.Device.connect(function (conn) {
	Client.connect(conn);
});

Twilio.Device.disconnect(function (conn) {
	Client.disconnect(conn);
});

Twilio.Device.incoming(function (conn) {
	console.log(conn);
	Client.incoming(conn);
});

$(function () {
	$('#client-ui-hangup').live('click', function() {
		Client.hangup();
	});
	$('button.client-button').live('click', function() {
		Client.ui.openWindow();
	});
	// only do status checks in the main window(s)
	if ($('#openvbx-logo').size()) {
		Client.status.displayOnlineStatus();
	}
	Client.init();
});

/////////////////////////////////////////////////////
// Call Dialog

$(function () {	
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
			clientDialPopup();
		}
		else {
			deviceDialNumber();
		}
	}
	
	var clientDialPopup = function() {
		var params = $('form input, form select', dialog).serializeArray();
		params.push({name: 'outgoing', value: 1});
		Client.ui.openWindow(params);
		$('.close', dialog).click();
	}
	
	var deviceDialNumber = function() {
		$('.invoke-call-button span').text('Calling...');
		$('.call-dialing').show();

		var link = $(this).data('link');
		$(this).prop('disabled', true);
		var button = $(this);
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
