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
		
	options: {
		cookie_name: 'vbx_client_call',
		check_timeout: 5000 // how often the parent window should check client window status
	},
	
	message: function (status) {
		console.log(status);
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
		this.ui.toggleCallView();
		this.connection = Twilio.Device.connect(params);
	},

	hangup: function () {
		if (this.connection) {
			this.connection.disconnect();
			this.connection = false;
		}
	},
	
// listeners

	incoming: function (connection) {
		window.focus();
		this.message('Incoming call from: ' + connection.parameters.From);
		if (!this.connection) {
			this.connection = connection;
			// notify user of incoming call in future versions
			Client.ui.toggleCallView();
			Client.ui.show('answer');	
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
		this.status.setCallStatus(false);
		this.message('Call ended');
		setTimeout('Client.ui.toggleCallView()', 3000);
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
		$('#client-ui-actions #client-ui-' + element).show().siblings().hide();
	},
	
	hide: function(element) {
		$('#client-ui-actions #client-ui-' + element).hide().siblings().hide();
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
	toggleCallView: function() {
		var dialer = $('#dialer'),
			dialer_offset = parseInt($('#dialer').css('width').replace('px', '')) + parseInt($('#dialer .client-ui-tab').css('width').replace('px', '')) + 'px';
		
		if (dialer.hasClass('closed')) {
			dialer_offset_mod = '+='
			dialer.removeClass('closed');
		}
		else {
			dialer_offset_mod = '-='
			dialer.addClass('closed');
		}

		dialer.animate({
			right: dialer_offset_mod + dialer_offset
		}, 
		500,
		function() {
			// TBD?
		});
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
		Client.ui.show('hangup');
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