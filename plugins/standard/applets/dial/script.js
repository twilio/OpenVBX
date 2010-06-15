$(document).ready(function(){
	// $(document).bind('applet-visible', function(event, instance) {
		// if(link.hasClass('standard---dial')) {
		// }
	// });

	var app = $('.flow-instance.standard---dial');

	// detect when voicemail widget user or group is chosen
	
	$(".dial-applet .usergroup-container", app).live('usergroup-selected', function(e, usergroup_label, type){
		// hide the a/s picker if it's a user, because they configure that in their personal settings, but show if it's a group
		if (type == 'group') {
			$(e.target).closest('.dial-applet').find('.group-voicemail').show();
			$(e.target).closest('.dial-applet').find('.personal-voicemail').hide();
		} else {
			$(e.target).closest('.dial-applet').find('.group-voicemail').hide();
			$(e.target).closest('.dial-applet').find('.personal-voicemail').show();
		}
	});

	$(".dial-applet input.dial-whom-selector-radio", app).live('change', function (e) {
		var value = $(e.target).val();
		
		var noAnswerAction = $(e.target).closest('.dial-applet').find('input.no-answer-action-radio');
		
		if (value == 'user-or-group') {
			$(e.target).closest('.dial-applet').find('.nobody-answers-user-group').removeClass('hide');
			$(e.target).closest('.dial-applet').find('.nobody-answers-number').addClass('hide');
			$(e.target).closest('.dial-applet').find('.voicemail-row').removeClass('hide');
		} else if (value == 'number') {
			$(e.target).closest('.dial-applet').find('.nobody-answers-user-group').addClass('hide');
			$(e.target).closest('.dial-applet').find('.nobody-answers-number').removeClass('hide');
			$(e.target).closest('.dial-applet').find('.voicemail-row').addClass('hide');
			
			// The user has selected to dial an arbitrary number and the voicemail
			// option isn't available for that mode.  So, if "Send to Voicemail" had
			// previously been selected, we need to choose something new.
		} else {
			alert("Unexpected value: " + value);
		}
	});

	// Highlights the region for radio-tables
	$(".dial-applet input.no-answer-action-radio,.dial-applet input.dial-whom-selector-radio", app).live('click', function(event) {
		var tr = $(this).closest('tr');
		$('tr', tr.closest('table')).each(function (index, element) {
			// Set the others to off
			$(element).removeClass('on').addClass('off');
		});
		
		tr.addClass('on').removeClass('off');
	});

});
