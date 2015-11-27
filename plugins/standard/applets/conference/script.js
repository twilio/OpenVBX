jQuery(function($) {

	// Highlights the region for radio-tables
	$(".flow-instance .conference-applet input.conference-record-selector-radio").live('click', function() {
		$(this).closest('tr').siblings('tr').each(function(i, elem) {
				$(elem).removeClass('on').addClass('off');
			})
			.end().addClass('on').removeClass('off');
	});

});