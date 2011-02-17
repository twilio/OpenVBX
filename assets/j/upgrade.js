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


OpenVBX.Upgrader = {
	tabsDisabled: true,
	ready : false,
	currentStep : 1,
	validate : function(afterValidation) {
		var step = $('#step-'+OpenVBX.Upgrader.currentStep);
		var params = $('textarea, input, select', step);
		var result = $.ajax({
			url : OpenVBX.home + 'upgrade/validate',
			data : params,
			success : function(data) {
				$('.invalid').removeClass('invalid');
				if(!data.success) {
					$('.error').text(data.message);
					$('.error').slideDown();
					for(var a in data.errors) {
						$('#'+a+'-'+OpenVBX.Upgrader.currentStep).addClass('invalid');
					}
				} else {
					if(OpenVBX.Upgrader.currentStep == 1) {
						OpenVBX.Upgrader.ready = true;
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

		if(OpenVBX.Upgrader.prevStepLock)
			return false;

		OpenVBX.Upgrader.prevStepLock = true;
		if($('.steps').css('left').replace('px','') > -700)
			return false;

		$('.error').slideUp();
		OpenVBX.Upgrader.currentStep -= 1;
		OpenVBX.Upgrader.gotoStep(OpenVBX.Upgrader.currentStep);

		return false;
	},
	nextStep : function(e) {
		OpenVBX.Upgrader.tabsDisabled = false;
		if(typeof(e) != "undefined") {
			e.preventDefault();
		}

		if(OpenVBX.Upgrader.nextStepLock)
			return false;

		var afterValidation = function() {

			OpenVBX.Upgrader.nextStepLock = true;
			if($('.steps').css('left').replace('px','') <= -3500)
				return false;

			$('.error').slideUp();
			OpenVBX.Upgrader.currentStep += 1;
			if(OpenVBX.Upgrader.currentStep == 2 && OpenVBX.Upgrader.ready) {
				OpenVBX.Upgrader.submit(e);
			} else {
				OpenVBX.Upgrader.gotoStep(OpenVBX.Upgrader.currentStep);
			}

		};

		OpenVBX.Upgrader.validate(afterValidation);

		return false;
	},
	setButtons : function() {
		if($('.steps').css('left').replace('px','') > -700) {
			$('button.prev').attr('disabled', 'disabled');
		} else {
			$('button.prev').removeAttr('disabled');
		}

		if($('.steps').css('left').replace('px','') <= -3500) {
			$('button.next').attr('disabled', 'disabled');
		} else {
			$('button.next').removeAttr('disabled');
		}
		switch(OpenVBX.Upgrader.currentStep) {
			case 1:
				$('button.prev').hide();
				$('button.next').hide();
				$('button.submit').show();
			    break;
			case 2:
				$('button').hide();
				break;
		}

		OpenVBX.Upgrader.nextStepLock = false;
		OpenVBX.Upgrader.prevStepLock = false;
	},
	gotoStep : function(step) {
		var left = (step * -700) + 700;
		$('.steps').animate({'left': left}, 'normal', 'swing', OpenVBX.Upgrader.setButtons);
		OpenVBX.Upgrader.currentStep = step;
	},
	toggleError : function() {
		$('.error').slideToggle();
	},
	submit : function(e) {
		if(typeof(e) != "undefined") {
			e.preventDefault();
		}

		if(OpenVBX.Upgrader.ready)
		{
			$.ajax({
				url : OpenVBX.home + 'upgrade/setup',
				data : $('form input, form select, form textarea'),
				success : function(data) {
					if(!data.success) {
						$('.error')
							.text(data.message)
							.slideDown();
					} else {
						OpenVBX.Upgrader.gotoStep(2);
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
		setTimeout(OpenVBX.Upgrader.toggleError,
				   1000);
	}

	OpenVBX.Upgrader.setButtons();
	$('button.next').click(OpenVBX.Upgrader.nextStep);
	$('button.prev').click(OpenVBX.Upgrader.prevStep);
	$('.error').click(OpenVBX.Upgrader.toggleError);
	$('button.submit').click(OpenVBX.Upgrader.nextStep);
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
					  OpenVBX.Upgrader.nextStep();
				  }
			  });
	});

	var last_key = false;
	$(window).bind('keydown', function(e) {
		var tabstops = {iDatabasePassword : '',
						iTwilioToken : '',
						iFromEmail : '',
						iAdminPw : ''};

		if($(e.target).attr('id') in tabstops
		   && e.which == 9 && last_key != 16)
			e.preventDefault();

		if(OpenVBX.Upgrader.tabsDisabled && e.which == 9)
			e.preventDefault();
		last_key = e.which;
	});

	setTimeout(function() {
		$.ajax({
			url : OpenVBX.home.replace('index.php', 'support/rewrite'),
			success : function(data, code) {
				$('input[name=rewrite_enabled]').attr("value", 1);
			},
			error : function(data) {
				$('input[name=rewrite_enabled]').attr("value", 0);
			}
		});
	}, 1000);

});