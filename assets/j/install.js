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

if(typeof(OpenVBX) == "undefined") {
	var OpenVBX = {};
}


OpenVBX.Installer = {
	tabsDisabled: true,
	ready : false,
	currentStep : 1,
	validate : function(afterValidation) {
		var step = $('#step-'+OpenVBX.Installer.currentStep);
		var params = $('textarea, input, select', step);
		var result = $.ajax({
			url : OpenVBX.home + '/install/validate',
			data : params,
			success : function(data) {
				$('.invalid').removeClass('invalid');
				if(!data.success) {
					$('.error').text(data.message);
					$('.error').slideDown();
					for(var a in data.errors) {
						$('#'+a+'-'+OpenVBX.Installer.currentStep).addClass('invalid');
					}
				} else {
					if(OpenVBX.Installer.currentStep == 5) {
						OpenVBX.Installer.ready = true;
					}
					
					afterValidation();
				}
				return data.success;
			},
			type : 'post',
			async : false,
			dataType : 'json',
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				$('.error').text('An application error occurred.  Please try again.');
				$('.error').slideDown();
			}
		});
		return result;
	},
	prevStep : function(e) {
		if(typeof(e) != "undefined") {
			e.preventDefault();
		}

		if(OpenVBX.Installer.prevStepLock)
			return false;

		OpenVBX.Installer.prevStepLock = true;
		if($('.steps').css('left').replace('px','') > -700)
			return false;

		$('.error').slideUp();
		OpenVBX.Installer.currentStep -= 1;
		OpenVBX.Installer.gotoStep(OpenVBX.Installer.currentStep);

		return false;
	},
	nextStep : function(e) {
		/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
		// Check for install tabs.
		if (OpenVBX.Installer.currentStep == 3) {
			if (!$('.install-tabs a:last').hasClass('install-tab-active')) {
				// Get the current active tab
				var newTab = null;
				var currentTab = null;
				var done = false;
				$('.install-tabs a').each(function() {
					if (done)
						return;
					if ($(this).hasClass('install-tab-active') && !currentTab) {
						currentTab = $(this);
					} else if (!$(this).hasClass('install-tab-active') && currentTab && !newTab) {
						newTab = $(this);
					} 
					if (currentTab && newTab && !done) {
						done = true;
						changeInstallTab(newTab);
						return;
					}
				});
				return false;
			}
		}
		/** End Disruptive Technologies code **/

		OpenVBX.Installer.tabsDisabled = false;
		if(typeof(e) != "undefined") {
			e.preventDefault();
		}

		if(OpenVBX.Installer.nextStepLock)
			return false;

		var afterValidation = function() {
			
			OpenVBX.Installer.nextStepLock = true;
			if($('.steps').css('left').replace('px','') <= -3500)
				return false;
			
			$('.error').slideUp();
			OpenVBX.Installer.currentStep += 1;
			if(OpenVBX.Installer.currentStep == 6 && OpenVBX.Installer.ready) {
				OpenVBX.Installer.submit(e);
			} else {
				OpenVBX.Installer.gotoStep(OpenVBX.Installer.currentStep);
			}

		};

		OpenVBX.Installer.validate(afterValidation);

		return false;
	},
	setButtons : function() {
		if($('.steps').css('left').replace('px','') > -700) {
			$('button.prev').prop('disabled', true);
		} else {
			$('button.prev').prop('disabled', false);
		}
		
		if($('.steps').css('left').replace('px','') <= -3500) {
			$('button.next').prop('disabled', true);
		} else {
			$('button.next').prop('disabled', false);
		}
		switch(OpenVBX.Installer.currentStep) {
			case 1:
				$('button.prev').hide();
				$('button.next').show();
				$('button.submit').hide();
			break;
			default:
				$('button.prev').show();
				$('button.next').show();
				$('button.submit').hide();
				break;
			case 5:
				$('button.next').hide();
				$('button.submit').show();
				break;
			case 6:
				$('button').hide();
				break;
		}

		OpenVBX.Installer.nextStepLock = false;
		OpenVBX.Installer.prevStepLock = false;
	},
	gotoStep : function(step) {
		var left = (step * -700) + 700;
		$('.steps').animate({'left': left}, 'normal', 'swing', OpenVBX.Installer.setButtons);
		OpenVBX.Installer.currentStep = step;
	},
	toggleError : function() {
		$('.error').slideToggle();
	},
	submit : function(e) {
		if(typeof(e) != "undefined") {
			e.preventDefault();
		}

		if(OpenVBX.Installer.ready)
		{
			$.ajax({
				url : OpenVBX.home + '/install/setup',
				data : $('form input, form select, form textarea'),
				success : function(data) {
					if(!data.success) {
						$('.error')
							.text(data.error)
							.slideDown();
					} else {
						OpenVBX.Installer.gotoStep(6);
					}
				},
				type : 'post',
				dataType : 'json',
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$('.error')
						.text('An application error occurred.  Please try again.')
					.slideDown();
				}
			});
		}

		return false;
	}
};

$(document).ready(function() {
	if($('.error').text() != '') {
		setTimeout(OpenVBX.Installer.toggleError, 
				   1000);
	}
	
	OpenVBX.Installer.setButtons();
	$('button.next').click(OpenVBX.Installer.nextStep);
	$('button.prev').click(OpenVBX.Installer.prevStep);
	$('.error').click(OpenVBX.Installer.toggleError);
	$('button.submit').click(OpenVBX.Installer.nextStep);
	$('form').submit(function(e) {
		e.preventDefault();
	});
	
	$('fieldset').each(function() { 
		$('input:last', 
		  this).keypress(
			  function(e) {
				  var keyCode = e.keyCode || e.which; 
				  if(keyCode == 9) {
					  e.preventDefault();
					  OpenVBX.Installer.nextStep();
				  }
			  });
	});

	var last_key = false;
	$(window).bind('keydown', function(e) {
		var tabstops = {iDatabasePassword : '', 
						iTwilioToken : '',
						iFromEmail : '',
						iAdminPw2 : ''};

		if($(e.target).attr('id') in tabstops
		   && e.which == 9 && last_key != 16)
			e.preventDefault();

		if(OpenVBX.Installer.tabsDisabled && e.which == 9)
			e.preventDefault();
		last_key = e.which;
	});

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

	/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
	changeInstallTab = function(newTab) {
		// Mark all tabs as inactive
		$('.install-tabs a').each(function() {
			$(this).removeClass("install-tab-active");
		});
		// Mark as active
		newTab.addClass("install-tab-active");
		// Slide up all tabs
		$('.install-tab:visible').each(function() {
			$(this).slideUp();
		});
		// Slide down the relevant install tab
		var installTabName = newTab.attr('id').replace('-header', '');
		$('#'+installTabName).slideDown();
		return false;
	}
	// Tropo install tabs
	$('.install-tab').hide();
	$('.install-tabs a').click(function() {
		changeInstallTab($(this));
	});
	$('.install-tab:first').show();
	$('.install-tabs a:first').addClass("install-tab-active");
	/** End Disruptive Technologies code **/


});