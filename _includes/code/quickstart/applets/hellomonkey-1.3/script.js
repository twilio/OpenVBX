jQuery(function($) {
	// Disable all the template row inputs
	$('.monkey-applet tr.hide input').attr('disabled', 'disabled');

	//This function will create a new row for the table based off 
	// the template in the footer
	$('.monkey-applet .action.add').live('click', function(event) {
		event.preventDefault();
		var row = $(this).closest('tr'),
			wrapper = $(this).parents('.monkey-applet'),
			newRow = $('tfoot tr', wrapper).html();
		
		newRow = $('<tr>' + newRow + '</tr>')
			.show()
			.insertAfter(row);
		
		$('.flowline-item', newRow).droppable(Flows.events.drop.options);
		$('td', newRow).flicker();	
		$('.flowline-item input', newRow).attr('name', 'choices[]');
		$('input.small', newRow).attr('name', 'keys[]');
		
		$('input:visible:first', newRow).removeAttr('disabled').focus();
		$(event.target).parents('.options-table').trigger('change');
		return false;
	});

	//This function removes rows with an animation
	$('.monkey-applet .action.remove').live('click', function() {
		var row = $(this).closest('tr'),
			bgColor = row.css('background-color');
			
		row.animate({
				backgroundColor : '#FEEEBD'
			}, 'fast')
			.fadeOut('fast', function() {
				row.remove();
			});

		return false;
	});
	
	
	$('.monkey-applet .options-table').live('change', function() {
		$('tbody tr', this).first()
			.find('.action.remove').hide();
	});

	$('.monkey-applet .options-table').trigger('change');
});
