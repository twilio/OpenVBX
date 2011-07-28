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
	connection: false,
	
	onready: function(){},
	
	incoming_timeout: null,
	
	muted: false,
		
	options: {
		cookie_name: 'vbx_client_call',
		check_timeout: 5000 // how often the parent window should check client window status
	},
	
	message: function (status) {
		//console.log(status);
		$('#client-ui-message').text(status);
	},
	
	init: function () {
		Twilio.Device.setup(OpenVBX.client_capability);

		Twilio.Device.ready(function (device) {
			Client.ready(device);
		});
		
		Twilio.Device.offline(function (device) {
			Client.offline(device);
		});
		
		Twilio.Device.error(function (error) {
			//console.log(error);
			Client.error(error);
		});
		
		Twilio.Device.connect(function (conn) {
			Client.connect(conn);
		});
		
		Twilio.Device.disconnect(function (conn) {
			//console.log('disconnect');
			Client.disconnect(conn);
		});
		
		Twilio.Device.incoming(function (conn) {
			//console.log('incoming');
			Client.incoming(conn);
		});
		
		Twilio.Device.cancel(function(conn) {
			//console.log('canceled');
			Client.cancel();
		});
		
		$('#dialer #client-ui-actions button').hide();
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
	
	call: function (params) {
		this.ui.toggleCallView('open');
		this.connection = Twilio.Device.connect(params);
	},

	hangup: function () {
		if (this.connection) {
			this.connection.disconnect();
			this.connection = false;
		}
	},
	
	mute: function() {
		if (this.connection) {
			this.muted = true;
			$('#client-ui-mute').addClass('muted').text('Unmute');
			this.connection.mute();
		}
	},
	
	unmute: function() {
		if (this.connection) {
			this.muted = false;
			$('#client-ui-mute').removeClass('muted').text('Mute');
			this.connection.unmute();
		}
	},
	
	togglemute: function() {
		if (this.connection) {
			if (this.muted) {
				this.unmute();
			}
			else {
				this.mute();
			}
		}
	},
	
	giveUpIncoming: function() {
		if (this.incoming) {
			console.log('unmuting');
			this.connection.cancel();
		}
		this.status.setCallStatus(false);
		setTimeout(function() { Client.ui.toggleCallView('close'); }, 1000);
	},
	
// listeners

	incoming: function (connection) {
		this.message('Incoming call from: ' + connection.parameters.From);
		if (!this.connection) {
			this.connection = connection;
			this.incoming_timeout = setTimeout('Client.giveUpIncoming()', 15000);
			// notify user of incoming call in future versions
			Client.ui.toggleCallView('open');
			Client.ui.show('.answer');	
		}
		else {
			this.ui.hide('button');
			connection.cancel();
		}
	},

	accept: function (connection) {
		clearTimeout(this.incoming_timeout);
		this.connection.accept();
	}, 

	error: function (error) {
		this.ui.endTick();
		this.status.setCallStatus(false);
		this.message(error);
		clearTimeout(this.incoming_timeout);
		this.connection = null;
	},

	connect: function (conn) {
		this.ui.startTick();
		this.ui.show('.hangup, .mute');
		this.ui.hide('.answer');
		this.status.setCallStatus(true);
		this.message('Calling');
		this.setOnBeforeUnload(true);
		clearTimeout(this.incoming_timeout);
	},

	disconnect: function (conn) {
		this.connection = null;
		this.ui.endTick();
		this.ui.hide('button');
		this.status.setCallStatus(false);
		this.message('Call ended');
		this.setOnBeforeUnload(false);
		setTimeout(function() { Client.ui.toggleCallView('close'); }, 3000);
		clearTimeout(this.incoming_timeout);
	},
	
	cancel: function(conn) {
		this.connection = null;
		this.ui.endTick();
		this.ui.hide('button');
		this.status.setCallStatus(false);
		this.message('Call cancelled');
		setTimeout(function() { Client.ui.toggleCallView('close'); }, 1000);
		clearTimeout(this.incoming_timeout);
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
	
	show: function(elements) {
		$(elements, $('#client-ui-actions')).show();
	},
	
	hide: function(elements) {
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
	
	// show & hide the dial pad
	toggleDialer: function() {
		
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
			function() {
				// TBD?
			});
			
		$('#client-ui-tab-status').animate({
				top: tab_status_offset_mod + tab_status_offset
			},
			animate_speed,
			function() {
				// TBD?
			});
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
				$('.client-ui-timer').text('0:00');
			});
		}
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

$(function () {
	$('#client-ui-answer').live('click', function() {
		Client.accept();
		Client.ui.show('.mute, .hangup');
	});

	$('#client-ui-dial').live('click', function() {
		var params = {
			'to': $('#client-ui-number').val(),
			'callerid': client_params.callerid,
			'Digits': 1
		}
		Client.call(params);
	});

	$('#client-ui-hangup').live('click', function() {
		Client.hangup();
	});
	
	$('#client-ui-mute').live('click', function() {
		Client.togglemute();
	});

	$('.client-ui-button').live('click', function(event) {
		event.stopPropagation();
		var key = $(this).children('.client-ui-button-number').text();
		Client.ui.pressKey(key);
	});
	
	$('#dialer .client-ui-tab-wedge a').live('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		Client.ui.toggleTab(this);
	});
	
	$('#dialer .client-ui-tab-status-inner').live('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		Client.ui.toggleTab(this);
	});
	
	Client.init();
});