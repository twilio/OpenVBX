$(document).ready(function() {
	// Disable all the template row inputs
	$('.query-applet tr.hide input').prop('disabled', true);

	$('.query-applet input.keypress').live('change', function(event) {
		var row = $(this).parents('tr');
		$('input[name^="responses"]', row).attr('name', 'keys['+$(this).val()+']');
	});

	$('.query-applet .action.add').live('click', function(event) {
		event.preventDefault();
		var row = $(this).closest('tr');
		var newRow = $('tfoot tr', $(this).parents('.query-applet')).html();
		newRow = $('<tr>' + newRow + '</tr>')			
			.show()
			.insertAfter(row);
		$('.flowline-item').droppable(Flows.events.drop.options);
		$('td', newRow).flicker();
		$('textarea.response', newRow).attr('name', 'responses[]');
		$('input.keypress', newRow).attr('name', 'keys[]');
		$('input', newRow).prop('disabled', false).focus();
		$(event.target).parents('.options-table').trigger('change');
		return false;
	});

	$('.query-applet .action.remove').live('click', function() {
		var row = $(this).closest('tr');
		var bgColor = row.css('background-color');
		row.animate(
			{
				backgroundColor : '#FEEEBD'
			}, 
			'fast')
			.fadeOut('fast', function() {
				row.remove();
			});

		return false;
	});

	$('.query-applet .options-table').live('change', function() {
		var first = $('tbody tr', this).first();
		$('.action.remove', first).hide();
	});

	$('.query-applet .options-table').trigger('change');
});
