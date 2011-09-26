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
		
	muted: false,
		
	options: {
		cookie_name: 'vbx_client_call'
	},
	
	init: function () {
		try {
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
				Client.incoming(conn);
			});
		
			Twilio.Device.cancel(function(conn) {
				Client.cancel();
			});
		
			$('#dialer #client-ui-actions button').hide();
		}
		catch (e) {
			this.disabled = true;
			// browser most likely doesn't have flash or is using a flash block application
			Client.ui.disabledBanner(e);
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

// Actions
	
	answer: function() {
		this.accept();
		this.ui.show_actions('.mute, .hangup');
	},
	
	call: function (params) {
		if (Twilio.Device.status() == 'ready') {
			this.ui.toggleCallView('open');
			this.connection = Twilio.Device.connect(params);
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
		if (connection == this.connection) {
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
		if (connection == this.connection) {
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
		if (typeof this.onready == 'function') {
			this.onready.call();
		}
	}
};

Client.ui = {
	reset: function() {
		// force reset all conditions
		Client.message('Ready');
		this.toggleCallView('close');
		this.hide_actions('button');
		this.endTick();
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

		$('.client-ui-timer').text(minutes + ':' + seconds);
	},
	
	// open & close the call tab
	toggleTab: function(clicked) {
		var tab = $(clicked).closest('.client-ui-tab'),
			animate_speed = 500,
			dialer_offset = $('#dialer .client-ui-content').css('width'),
			tab_status_offset = $('#dialer .client-ui-tab').css('height')
		
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
	},
	
	// show hide the dial tab/status slider
	toggleCallView: function(status) {
		var dialer = $('#dialer'),
			dialer_offset_mod = false,
			dialer_offset = parseInt($('#dialer').css('width').replace('px', '')) + parseInt($('#dialer .client-ui-tab').css('width').replace('px', '')) + 'px';
		
		if (status == 'open' && dialer.hasClass('closed')) {
			dialer_offset_mod = '+=';
			dialer.removeClass('closed');
		}
		else if (status == 'close' && !dialer.hasClass('closed')) {
			dialer_offset_mod = '-=';
			dialer.addClass('closed');
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
			});
		}
	},
	
// banner to show that client is disabled
	disabledBanner: function(exception) {
		var err_message = '<p><b>An error has occurred while initializing the Phone Client:</b><br />' +
							exception.message + '</p>';
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
		this.getCookieVal(on_call);
	},
	
	setWindowStatus: function (status) {
		this.setCookieVal('window_open', status);
		$.ajax({
			url: OpenVBX.home + '/account/edit',
			data: {
				'online': (status ? 1 : 0).toString()
			},
			success: function(r) {},
			async: false,
			type : 'POST',
			dataType : 'json'
		});
	},
	
	getWindowStatus: function () {
		return this.getCookieVal('window_open');
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
	$('#client-ui-answer').live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		Client.answer();
	});

	$('#client-ui-hangup, #client-ui-close').live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		Client.hangup();
	});
	
	$('#client-ui-mute').live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		Client.togglemute();
	});

	$('.client-ui-button').live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		var key = $(this).children('.client-ui-button-number').text();
		Client.ui.pressKey(key);
	});
	
	$('.client-ui-tab-wedge a, .client-ui-tab-status-inner', $('#dialer')).live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		Client.ui.toggleTab(this);
	});
	
	Client.init();
});