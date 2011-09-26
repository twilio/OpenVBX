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

var _st = window.setTimeout;

window.setTimeout = function(fRef, mDelay) {
	if(typeof fRef == "function") {
		var argu = Array.prototype.slice.call(arguments,2);
		var f = (function(){ fRef.apply(null, argu); });
		return _st(f, mDelay);
	}
	return _st(fRef,mDelay);
}

$.fn.extend({
	left: function(value) {
		if (value === undefined) {
			return $(this).offset().left;
		} else {
			$(this).css('left', value);
		}
	},
	top: function(value) {
		if (value === undefined) {
			return $(this).offset().top;
		} else {
			$(this).css('top', value);
		}
	},
	right: function(value) {
		if (value === undefined) {
			return $(this).offset().left + $(this).outerWidth(true);
		} else {
			$(this).css('left', (value - $(this).outerWidth(true)));
		}
	},
	bottom: function(value) {
		if (value === undefined) {
			return $(this).offset().top + $(this).outerHeight(true);
		} else {
			$(this).css('top', (value - $(this).outerHeight(true)));
		}
	},
	frame: function() {
		return "{ " + $(this).left() + ", " + $(this).top() + ", " + $(this).outerWidth(true) + ", " + $(this).outerHeight(true) + " }";
	}
});

function preventDefault(e) {
	e.preventDefault();
}

function convertTimeToString(time, midnightToday, firstOfTheYear) {
	
	// Callers can choose to pass in these reference points, which saves us from having
	// to calculate them each time.
	if (!midnightToday || !firstOfTheYear) {
		var now = new Date();
		midnightToday = new Date(now.getFullYear() , now.getMonth(), now.getDate(), 0, 0, 0).getTime();
		firstOfTheYear = new Date(now.getFullYear(), 0, 1, 0, 0, 0).getTime();
	}
	
	var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
	
	var date = new Date(time);
	
	if (time > midnightToday) {
		// Show hours and mins

		var hours = date.getHours();
		var mins = date.getMinutes();

		var amOrPm = (hours < 12) ? "am" : "pm";

		if (hours > 12) {
			hours -= 12;
		}
		
		if (hours == 0) {
			hours = 12;
		}

		return hours + ":" + (mins < 10 ? "0" : "") + mins + " " + amOrPm;
	} else if (time > firstOfTheYear) {
		// show MMM D
		
		return months[date.getMonth()] + " " + date.getDate();
	} else {
		// show M/D/YY
		var year = (date.getYear() - 100);
		
		return date.getMonth() + "/" + date.getDay() + "/" + (year < 10 ? "0" : "") + year;
	}
}

$(document).ready(function() {
	$('button').mouseover(function() {
			$(this).addClass('ui-state-hover');
		})
		.mouseout(function() {
			$(this).removeClass('ui-state-hover');
		})
		.mousedown(function() {
			$(this).addClass('ui-state-focus');
		})
		.mouseup(function() {
			$(this).removeClass('ui-state-focus');
		});

		
/* jQuery.values: get or set all of the name/value pairs from child input controls
 * @argument data {array} If included, will populate all child controls.
 * @returns element if data was provided, or array of values if not
*/
	$.fn.values = function(data) {
		var els = $(this).find(':input').get();

		if(typeof data != 'object') {
			// return all data
			data = {};

			$.each(els, function() {
				if (this.name && !this.disabled && (this.checked
								|| /select|textarea/i.test(this.nodeName)
								|| /text|hidden|password/i.test(this.type))) {
					data[this.name] = $(this).val();
				}
			});
			return data;
		} else {
			$.each(els, function() {
				if (this.name && data[this.name]) {
					if(this.type == 'checkbox' || this.type == 'radio') {
						this.checked = (data[this.name] == $(this).val());
					} else {
						$(this).val(data[this.name]);
					}
				}
			});
			return $(this);
		}
	};

	$.fn.fadeRemove = function(speed) {
		return $(this).fadeOut(speed, function() { $(this).remove(); });
	}

	$.postJSON = function(url, data, callback) {
		if(url.indexOf('http://') < 0 ) url = OpenVBX.home + '/' + url;
		$.post(url, data, callback, "json");
	};

	$.extend($.ui.dialog.defaults, {
		autoOpen: false,
		closeOnEscape: true,
		closeText: '',
		draggable: true,
		height: 'auto',
		modal: true,
		position: 'center',
		resizable: false,
		open : function() {
			$('button').each(function() {
				if($(this).text().match(/cancel/i)) {
					$(this).addClass('cancel');
				}
			});
		}
	});
	
	$('.tabs').tabs();

	// This next bit allows us to dynamically load plugins only if we find them on the page
	var plugins = [
		{ 
			name: 'tabs',
			selector: '.tabs',
			css: 'c/ui.tabs.css' 
		}
	];
	
	$.each(plugins, function() {
		if($(this.selector).length > 0) {
			if(this.css) {
				$("<link>").appendTo("head").attr({
					rel:  "stylesheet",
					type: "text/css",
					href: OpenVBX.assets + '/' + this.css
				});
			}
		}
	});

	/* dynamically add classes to radio and checkboxes and their labels */

	$(':radio, :checkbox').live('click', function() {
		var hasChecked = $(this).hasClass('checked');

		if(this.checked) {

			if(this.name && $(this).attr('type') == 'radio') {
				// remove all siblings that are also checked
				$(this).siblings('.checked').removeClass('checked');
			}
			$(this).addClass('checked');
			$('label[for="' + this.id + '"]').addClass('checked');
		} else if(hasChecked) {
			$(this).removeClass('checked');
			$('label[for="' + this.id + '"]').removeClass('checked');
		}
	});

	$('.error-dialog').dialog({ 
		autoOpen: false,
		bgiframe: true,
		resizable: false,
		modal: true,
		buttons: {
			'Okay': function() {
				$(this).dialog('close');
			}
		}
	});

	$.ajaxSetup ({
		dataType : 'json',
		cache : false
	});

	$(document).ajaxError(function(event, request, settings, error) {
		$('.error-dialog').dialog('option', 'buttons', { 
			"Ok": function() { 
				$(this).dialog("close"); 
			} 
		});

		$('.error-dialog .error-code').text('');
		$('.error-dialog .error-message')
			.text('An unknown error occurred.  Please contact your OpenVBX provider.  Unable to complete request: ' 
				  + settings.url
				 );
				 
		$('.error-dialog').dialog('open');
	});
});

$(document).ready(function() {
	if($.browser.msie && $.browser.version < 7.0) {
		$('.error-dialog').attr('title', 'Unsupported Browser');
		$('.error-dialog .error-code').text('');
		$('.error-dialog .error-message')
			.text('Microsoft Internet Explorer is not currently supported.  It is currently under development and will be supported in the near future.  We recommend you use Mozilla Firefox, Safari, or Chrome at this time');
		$('.error-dialog').dialog('open');
	}


	$('.shout-out .close').click(function() {
        $('.shout-out').hide();
		$.cookie("mobile-app","false", { path: '/'});
    });
    
    $('#client-first-run a.dismiss').live('click', function(e) {
    	e.preventDefault();
    	e.stopPropagation();
    	
    	var display = $('#client-first-run'),
    		status = $('#vbx-client-status').hasClass('online');
    	
    	$.ajax({
    		url: OpenVBX.home + '/account/edit',
    		data: {
    			'online': (status ? 1 : 0).toString()
    		},
    		success: function(response) {
 				display.slideUp('3000');
    		},
    		type: 'POST',
    		dataType: 'json'
    	});
    });

	var mobileAppCookie = $.cookie("mobile-app");
	mobileAppCookie == "false" ? $('.shout-out').hide() : $('.shout-out').show();
});
