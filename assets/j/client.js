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
var Client = {
	// if we've detected no-flash, or flashblock, or ???
	disabled: false,
		
	connection: false,
	
	// custom onready function for any plugins to extend
	// not terribly useful until we can accept an array of
	// callbacks for multiple plugins to extend the behavior
	onready: function(){},
	
	// when an incoming call starts rining, we set a timeout that waits
	// and auto-cancels after a certain time limit
	incoming_timeout: null,
	
	// after a call ends we set a timeout that waits a little while before
	// putting the window away
	close_timeout: null,
	
	clients: [],
	
	// how we're calling. Via client, or via traditional phone hookup dance
	call_mode: null,
	
	muted: false,
		
	options: {
		cookie_name: 'vbx_client_call',
		debug: false
	},
	
	init: function (callback) {		
		try {
			Twilio.Device.setup(OpenVBX.client_capability, OpenVBX.client_params);

			Twilio.Device.ready(function (device) {
				Client.log('event: ready');
				Client.ready(device);
			});
		
			Twilio.Device.offline(function (device) {
				Client.log('event: offline');
				Client.offline(device);
			});
		
			Twilio.Device.error(function (error) {
				Client.log('event: error');
				Client.error(error);
			});
		
			Twilio.Device.connect(function (conn) {
				Client.log('event: connect');
				Client.connect(conn);
			});
		
			Twilio.Device.disconnect(function (conn) {
				Client.log('event: disconnect');
				Client.disconnect(conn);
			});
		
			Twilio.Device.incoming(function (conn) {
				Client.log('event: incoming');
				Client.incoming(conn);
			});
		
			Twilio.Device.cancel(function(conn) {
				Client.log('event: cancel');
				Client.cancel(conn);
			});
			
			Twilio.Device.presence(function(event) {
				Client.log('event: presence');
				Client.handleEvent(event);
			});
		
			$('#dialer #client-ui-actions button').hide();
		}
		catch (e) {
			this.disabled = true;
			// browser most likely doesn't have flash or is using a flash block application
			Client.ui.disabledBanner(e);
		}		
	},
	
	log : function(message) {
		if (Client.options.debug && window.console && window.console.log) {
			console.log(message);
		}
	},

// Helpers
	
	message: function (status) {
		$('#client-ui-message').text(status);
	},
	
	setOnBeforeUnload: function(status) {
		window.onbeforeunload = (status) ? this.onBeforeUnloadWarning : null;
	},
	
	onBeforeUnloadWarning: function() {
		return 'You are currently on a call. Refreshing this page will cause the call to drop. Do you really want to leave this page?';
	},
	
	translateKeyCode: function (code) {
		var number = code - 48;
		
		switch(number) {
			case -13:
				return '#';
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
	
	isReady: function() {
		var status = false;
		try {
		 	status = (Twilio.Device.status() == 'ready');
		}
		catch (e) {
			// most likely the connection is delayed, everyone can just wait.
		}

		return status;
	},

	triggerError: function(message) {
		try {
			if (window.frames['openvbx-iframe'].OpenVBX.error) {
				window.frames['openvbx-iframe'].OpenVBX.error.trigger(message);
			}
		}
		catch (e) {
			Client.log(message);
		}
	},
	
// Actions
	
	handleEvent: function(event) {
		var pos = jQuery.inArray(event.from, Client.clients);
		if (event.available == true) {
			if (pos < 0) {
				Client.clients.push(event.from);
			}
		}
		else {
			if (pos > -1) {
				Client.clients.splice(pos, 1);
			}
		}
		
		// notify the iframe
		try {
			if (window.frames['openvbx-iframe'].OpenVBX.presence) {
				window.frames['openvbx-iframe'].OpenVBX.presence._set(event, Client.clients);
			}
		}
		catch (e) {
			// fail silently, probably tried during a page load or something fun like that
		}

		// trigger event for main frame listeners
		$(this).trigger('presence', [event, Client.clients]);
	},
	
	setCallMode: function() {
		Client.call_mode = $('#client-mode-status').val();	
	},
	
	getCallMode: function() {
		if (!Client.call_mode) {
			Client.setCallMode();
		}
		return Client.call_mode;
	},
	
	// "magic" caller that analyzes the environment to make appropriate call
	makeCallTo: function(call_to, online_status) {
		var mode = Client.getCallMode(),
			status = online_status == 'online' ? 'online' : 'offline',
			call_from = $('#caller-id-phone-number').val();
		if (mode == 'client' || mode == 'browser') {
			Client.call({
					to: call_to,
					callerid: call_from,
					Digits: '1',
					online: status
				}, true);
		}
		else {
			Client.dial({
				to: call_to,
				callerid: call_from,
				online: status,
				from: $('#client-mode-status').val()
			});
		}
	},
	
	dial: function(params) {
		// ajax call to trigger call connection dance
		$.ajax({
			url: OpenVBX.home + '/messages/call',
			data: params,
			dataType: 'json',
			type: 'POST',
			success: function(response) {
				if (response.error) {
					var message = 'Unable to complete call. Message from server: ' +
					 				response.message;
					Client.triggerError(message);
				}
				
				setTimeout(function() {
						Client.ui.toggleCallView('close');
					}, 1000);
			},
			error: function(xhr, status, error) {
				var message = 'Unable to complete call. Message from server: ' + error;
				Client.triggerError(message);
			}
		});
	},
	
	answer: function() {
		this.accept();
		this.ui.show_actions('.mute, .hangup');
	},
	
	call: function (params) {
		if (Twilio.Device.status() == 'ready') {
			$.post(OpenVBX.home + '/account/rest_access_token', {},
				function(r) {
					if (!r.error) {
						params.rest_access = r.token;						
						Client.ui.toggleCallView('open', true);
						Client.connection = Twilio.Device.connect(params);
					}
					else {
						Client.triggerError(r.message);
					}
				},
				'json'
			);
		}
	},

	hangup: function () {
		if (this.connection) {
			this.connection.disconnect();
		}
		else {
			this.ui.toggleCallView('close');
		}
	},
	
	mute: function() {
		if (this.connection && this.connection.status() == 'open' && !this.muted) {
			this.muted = true;
			$('#client-ui-mute').addClass('muted').text('Unmute');
			this.connection.mute();
		}
	},
	
	unmute: function() {
		if (this.connection && this.connection.status() == 'open' && this.muted) {
			this.muted = false;
			$('#client-ui-mute').removeClass('muted').text('Mute');
			this.connection.unmute();
		}
	},
	
	togglemute: function() {
		if (this.connection && this.connection.status() == 'open') {
			if (this.muted) {
				this.unmute();
			}
			else {
				this.mute();
			}
		}
	},
	
	giveUpIncoming: function(conn) {
		conn.cancel();
		clearTimeout(this.incoming_timeout);
		setTimeout(function() {
				Client.ui.reset(); 
			}, 1000);
	},
	
	clear_connection: function() {
		if (this.connection) {
			// force drop the connection (happens during error handling)
			this.connection.disconnect(function(){});
			this.connection.disconnect();
		}
		this.connection = null;
	},
	
// listeners

	incoming: function (connection) {
		if (this.connection && this.connection.status() != 'closed') {
			connection.cancel();
			return;
		}
		
		this.connection = connection;
		
		clearTimeout(this.incoming_timeout);
		clearTimeout(this.close_timeout);
		this.incoming_timeout = setTimeout(function() {
				var conn = connection;
				Client.giveUpIncoming(conn);
			}, 15000);
				
		// Notification Message
		var incoming_message = 'Incoming Call';
		if (this.connection.parameters.From) {
			// From doesn't always get passed
			incoming_message += ' From: ' + this.connection.parameters.From;
		}
		this.message(incoming_message);
		
		// Show UI
		Client.ui.hide_actions('button');
		Client.ui.show_actions('.answer');
		Client.ui.toggleCallViewState('call');
		Client.ui.toggleCallView('open');
	},

	accept: function () {
		this.connection.accept();
		this.status.setCallStatus(true);
		
		Twilio.Device.sounds.incoming(false);
		
		var connection_message = 'Connected';
		if (this.connection.parameters.From) {
			connection_message += ' To: ' + this.connection.parameters.From;
		}
		this.message(connection_message);
		
		clearTimeout(this.close_timeout);
		clearTimeout(this.incoming_timeout);

		this.ui.hide_actions('.answer');		
	}, 

	error: function (error) {
		this.ui.endTick();
		this.status.setCallStatus(false);
		this.message(error.message);
		
		// dismiss incoming dial auto-dismiss action
		clearTimeout(this.incoming_timeout);
		
		// unset connection reference
		this.clear_connection();
		this.ui.hide_actions('button');
		this.ui.show_actions('.close');
	},

	connect: function (connection) {
		Twilio.Device.sounds.incoming(false);
		
		this.ui.startTick();
		this.ui.hide_actions('button');
		this.ui.show_actions('.hangup, .mute');

		var message = 'Call in Progress';
		if (connection.parameters.From) {
			message += ' with ' + connection.parameters.From;
		}
		
		this.message(message);

		this.status.setCallStatus(true);
		
		// dismiss incoming dial auto-dismiss action
		clearTimeout(this.incoming_timeout);
		clearTimeout(this.close_timeout);
	},

	disconnect: function (connection) {
		if (!this.connection) {
			return;
		}
		
		if (connection.parameters.CallSid == this.connection.parameters.CallSid) {
			Twilio.Device.sounds.incoming(true);
			
			// reset ui
			this.ui.endTick();
			this.ui.hide_actions('button');
			this.status.setCallStatus(false);
			this.message('Call ended');
		
			this.unmute();		
			this.clear_connection();
			clearTimeout(this.incoming_timeout);
		
			this.close_timeout = setTimeout(function() { 
					Client.ui.toggleCallView('close');
				}, 3000);
		}
	},
	
	cancel: function(connection) {
		if (!this.connection) {
			return;
		}
		
		if (connection.parameters.CallSid == this.connection.parameters.CallSid) {
			this.clear_connection();
		
			this.ui.endTick();
			this.ui.hide_actions('button');
			this.status.setCallStatus(false);
			this.message('Call cancelled');
			
			clearTimeout(this.incoming_timeout);
			setTimeout(function() {
					Client.ui.reset(); 
				}, 1000);
		}
	},

	offline: function (device) {
		this.status.setCallStatus(false);
		this.message("Offline");
	},

	ready: function (device) {
		this.message('Ready');
		this.status.setCallStatus(false);
		$('#client-ui-dial').show();
		if ($.type(this.onready) == 'function') {
			this.onready.call();
		}
	}
};

Client.ui = {
	reset: function() {
		// force reset all conditions
		Client.message('Ready');
		this.toggleCallView('close');
		this.toggleCallViewState('dial');
		this.hide_actions('button');
		$('#dial-phone-number').val('');
		this.endTick();
	},
	
	state : function() {
		var state = $('#dialer').hasClass('open') ? 'open' : 'closed';
		if (state == 'open') {
			state = $('#dialer .client-ui-tab').hasClass('open') ? 'open' : 'tab';
		}
		return state;
	},

// Buttons	
	pressKey: function(key) {
		$('#client-ui-number').focus().val($('#client-ui-number').val() + key);
		if(!Client.connection || !key) {
			return;
		}
		Client.connection.sendDigits(key);
	},
	
	show_actions: function(elements) {
		$(elements, $('#client-ui-actions')).show();
	},
	
	hide_actions: function(elements) {
		$(elements, $('#client-ui-actions')).removeClass('muted').hide();
	},
	
	addDialSpinner: function(elm, text) {
		var button = $(elm),
			span = $('<span />').addClass('button-spinner'),
			img = $('<img />').attr('src', OpenVBX.assets + '/assets/i/ajax-loader-circle-dark.gif');
			
		span.append(img);
		if (!text) {
			text = 'Calling';
		}
		span.append(text);
		button.find('.button-text').css({'display': 'none'})
			.end().append(span);
	},
	
	removeDialSpinner: function(elm) {
		$(elm).find('.button-spinner').remove()
			.end().find('button-text').show();
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
		var totalseconds = Math.floor(this.getTicks() / 1000);

		var minutes = Math.floor(totalseconds / 60);
		var seconds = totalseconds % 60;

		if(minutes < 10) {
			minutes = '0' + minutes;
		}

		if(seconds < 10) {
			seconds = '0' + seconds;
		}

		$('.client-ui-timer').text(minutes + ':' + seconds);
	},
	
	// open & close the call tab
	toggleTab: function(clicked) {
		var dialer = $('#dialer'),
			tab = $('.client-ui-tab', dialer),
			animate_speed = 500,
			dialer_offset = $('#dialer .client-ui-content').css('width'),
			tab_status_offset = $('#dialer .client-ui-tab').css('height');
		
		if (!dialer.hasClass('closed')) {
			if (tab.hasClass('open')) {
				dialer_offset_mod = '-=';
				tab_status_offset_mod = '-=';
				tab.removeClass('open');
			}
			else {
				dialer_offset_mod = '+=';
				tab_status_offset_mod = '+=';
				tab.addClass('open');
			}
		
			$('#dialer').animate({
					right: dialer_offset_mod + dialer_offset
				},
				animate_speed,
				function() {});
			
			$('#client-ui-tab-status').animate({
					top: tab_status_offset_mod + tab_status_offset
				},
				animate_speed,
				function() {});
		}
	},
	
	// show hide the dial tab/status slider
	toggleCallView: function(status, calling) {
		var dialer = $('#dialer'),
			dialer_offset_mod = false, // by default we don't want to move
			dialer_offset = parseInt($('#dialer').css('width').replace('px', ''), 10) +
			 				parseInt($('#dialer .client-ui-tab').css('width').replace('px', ''), 10)
			 				+ 'px';
		
		if (status == 'open' && dialer.hasClass('closed')) {
			dialer_offset_mod = '+=';
			dialer.removeClass('closed').addClass('open');
		}
		else if (status == 'close' && !dialer.hasClass('closed')) {
			dialer_offset_mod = '-=';
			dialer.addClass('closed').removeClass('open');
		}
		
		if (calling == true) {
			// pre-switch to dial-pad
			var state = calling ? 'call' : 'dial';
			Client.ui.toggleCallViewState(state);
		}

		if (dialer_offset_mod != false) {
			dialer.animate({
				right: dialer_offset_mod + dialer_offset
			}, 
			500,
			function() {
				$('.client-ui-timer').text('00:00');
				if (status == 'close') {
					Client.ui.reset();
				}
				else {
					$('#dial-phone-number').focus();
				}
			});
		}
	},
	
	// toggle between the in-call view & dial (choose who to call) view
	toggleCallViewState: function(state) {
		var chooser = $('#client-make-call'),
			callview = $('#client-on-call');
		if (state == 'call') {
			// we're ready to call
			chooser.hide();
			callview.show();
		}
		else if (state == 'dial') {
			// we need to choose who to call
			chooser.show();
			callview.hide();
		}
	},
	
	// switch the dial mode (phone vs. client)
	toggleCallMode: function(clicked) {
		var _this = $(clicked);
		if (!_this.hasClass('enabled')) {
			// Button state
			_this.addClass('enabled').removeClass('disabled')
				.siblings('a').removeClass('enabled').addClass('disabled');
			// status text state
			$('#client-mode-status-text #' + _this.attr('id') + '-text')
				.addClass('enabled').removeClass('disabled')
				.siblings().addClass('disabled').removeClass('enabled');
		}
		Client.setCallMode();
	},
	
	// toggle status by classname for a user in the list
	toggleUserStatus: function(userid, available) {
		var	user = $('#client-ui-user-list li#user-' + userid);
		if (available) {
			user.addClass('online');
		}
		else {
			user.removeClass('online');
		}
	},
	
	// when someone purchases a number we need to make it available
	refreshNumbers: function(number) {
		$.ajax({
			url: OpenVBX.home + '/numbers/refresh_select',
			data: {},
			dataType: 'json',
			type: 'POST',
			success: function(response) {
				if (response.error) {
					Client.triggerError('Unable to refresh phone numbers. Message from server: ' 
											+ reponse.message);
				}
				else {
					$('#dialer #callerid-container').html(response.html);
				}
			}
		});
	},
	
	refreshDevices: function() {
		$.ajax({
			url: OpenVBX.home + '/devices/refresh_dialer',
			data: {},
			dataType: 'json',
			type: 'POST',
			success: function(response) {
				if (response.error) {
					Client.triggerError('Unable to refresh devices. Message from server:'
											+ response.message);
				}
				else {
					$('#dialer #client-mode-status').replaceWith($(response.html));
				}
			}
		});
	},
	
	refreshUsers: function() {
		$.ajax({
			url: OpenVBX.home + '/accounts/refresh_dialer',
			data: {},
			dataType: 'json',
			type: 'POST',
			success: function(response) {
				if (response.error) {
					Client.triggerError('Unable to refresh users. Message from server: '
											+ response.message);
				}
				else {
					$('#dialer #client-ui-user-list').replaceWith($(response.html));
				}
			}
		});
	},
	
// user specific settings
	toggleOptionsSummary: function(clicked) {
		var toggle = $(clicked).find('#summary-call-toggle'),
			inputs = $(clicked).siblings('#call-options-inputs');
		if (toggle.hasClass('open')) {
			toggle.removeClass('open').html('&raquo;');
			inputs.slideUp('fast');
		}
		else {
			toggle.addClass('open').html('&laquo;');
			inputs.slideDown();
		}
	},
	
	toggleOptionsDescription: function() {
		var callerid = $('#caller-id-phone-number').val(),
			device = $('#client-mode-status option:selected');

		if (device.val() == 'browser') {
			$('#call-option-description-browser').show().siblings().hide();
		}
		else {
			var device_info = $.parseJSON(device.attr('data-device'));
			$('#call-option-description-device')
				.find('.device-number').html(device_info.number)
				.end().show().siblings().hide();			
		}
		$('#call-option-description-caller-id').text(callerid);
	},
	
	saveUserSettings: function(elm) {
		var input = $(elm),
			container = $(elm).closest('#call-options').find('#call-options-summary');
		
		// update the UI
		switch (input.attr('id')) {
			case 'caller-id-phone-number':
				// update the caller id display
				$('#summary-caller-id span').text(input.val());
				break;
			case 'client-mode-status':
				// update the icon representing the mode
				$('#summary-call-using').attr('class', input.val());
				break;
		}
		
		var new_settings = {};
		$('#call-options-inputs :input').prop('disabled', true)
			.each(function() {
				var option_input = $(this);
				new_settings[option_input.attr('name')] = option_input.val();
			});
		
		$.post(OpenVBX.home + '/account/settings',
			{
				settings: new_settings
			},
			function (response) {
				if (response.error) {
					Client.triggerError(response.message);
				}
				$('#call-options-inputs :input').prop('disabled', false);
				Client.ui.toggleOptionsDescription();
				Client.setCallMode();
			},
			'json'
		);
	},
	
// banner to show that client is disabled
	disabledBanner: function(exception) {
		var err_message = '<p><b>An error has occurred while initializing the Phone Client:</b>' +
							'<br />' + exception.message + '</p>';
		$('body').append($('<div id="client-error"><div>' + err_message + '</div></div>'));
	}
};

Client.status = {	
	setCallStatus: function (status) {
		// set warning message if user tries to refresh the browser
		Client.setOnBeforeUnload(status);
		this.setCookieVal('on_call', status);
	},
	
	getCallStatus: function () {
		return this.getCookieVal('on_call');
	},
	
	setWindowStatus: function (status, callback) {
		this.setCookieVal('window_open', status);
		$.ajax({
			url: OpenVBX.home + '/account/client_status',
			data: {
				'online': (status ? 1 : 0).toString(),
				'clientstatus' : true
			},
			success: function(response) {
				if (response.error) {}
				callback.apply(null, [response]);
			},
			async: false,
			type : 'POST',
			dataType : 'json'
		});
	},

// Cookie Helpers
	
	setCookieVal: function (key, val) {
		var cookie_val = this.getCookie();
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
	}
};

$(function () {
	var dialer = $('#dialer');
	var stopEvent = function(event) {
		event.preventDefault();
		event.stopPropagation();
	};
	
	// Answer Call button clicked
	$('#client-ui-answer', dialer).live('click', function(event) {
		stopEvent(event);
		Client.answer();
	});

	// Hangup Call button clicked
	$('#client-ui-hangup, #client-ui-close', dialer).live('click', function(event) {
		stopEvent(event);
		Client.hangup();
	});

	// Mute Call button clicked
	$('#client-ui-mute', dialer).live('click', function(event) {
		stopEvent(event);
		Client.togglemute();
	});

	// Button on Keypad clicked
	$('.client-ui-button', dialer).live('click', function(event) {
		stopEvent(event);
		var key = $(this).children('.client-ui-button-number').text();
		Client.ui.pressKey(key);
	});

	// Dialer tab clicked
	$('.client-ui-tab-wedge a, .client-ui-tab-status-inner', dialer).live('click', function(event) {
		stopEvent(event);
		if (Client.isReady()) {
			Client.ui.toggleCallView('close');
		}
		else {
			Client.ui.toggleTab(this);
		}
	});
	
	// Dial button on custom input form clicked
	$('#make-call-form', dialer).live('submit', function(event) {
		stopEvent(event);
		Client.makeCallTo($('#dial-phone-number').val());
	});
	$('#dial-input-button', dialer).live('click', function(event) {
		stopEvent(event);
		$('#make-call-form').submit();
	});
	
	// Dial button on user list clicked
	$('.user-dial-button', dialer).live('click', function(event) {
		stopEvent(event);
		var to = $(this).closest('li').find('input[name="email"]').val(),
			online_status = $(this).closest('li').hasClass('online') ? 'online' : 'offline';
		Client.makeCallTo(to, online_status);
	});
	
	// "Client"/"Phone" toggle clicked
	$('#client-mode-status a', dialer).live('click', function(event) {
		stopEvent(event);
		Client.ui.toggleCallMode(this);
	});
	
	// init presence
	
	// bind to event handler on Client object to get presence events
	$(Client).bind('presence', function(e, event, clients) {
		var userid = event.from.replace('client:', '');
		Client.ui.toggleUserStatus(userid, event.available);
	});
	
	if (OpenVBX.client_capability) {
		Client.setCallMode();
		Client.init();
	}
	
	$('#call-options-summary').live('click', function(e) {
		stopEvent(e);
		Client.ui.toggleOptionsSummary(this);
	});
	
	$('#call-options-inputs :input').live('change', function(e) {
		Client.ui.saveUserSettings($(this));
		return true;
	});
	
	Client.ui.toggleOptionsDescription();
});