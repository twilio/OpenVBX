$(document).ready(function() {
	// Disable all the template row inputs
	$('.menu-applet tr.hide input').prop('disabled', true);

	var app = $('.flow-instance.standard---menu');
	$('.menu-applet .menu-prompt .audio-choice', app).live('save', function(event, mode, value) {
		var text = '';
		if(mode == 'say') {
			text = value;
		} else {
			text = 'Play';
		}
		
		var instance = $(event.target).parents('.flow-instance.standard---menu');
		if(text.length > 0) {
			$(instance).trigger('set-name', 'Menu: ' + text.substr(0, 6) + '...');
		}
	});

	$('.menu-applet input.keypress').live('change', function(event) {
		var row = $(this).parents('tr');
		$('input[name^="choices"]', row).attr('name', 'choices['+$(this).val()+']');
	});

	$('.menu-applet .action.add').live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		var row = $(this).closest('tr');
		var newRow = $('tfoot tr', $(this).parents('.menu-applet')).html();
		newRow = $('<tr>' + newRow + '</tr>')			
			.show()
			.insertAfter(row);
		$('.flowline-item').droppable(Flows.events.drop.options);
		$('td', newRow).flicker();
		$('.flowline-item input', newRow).attr('name', 'choices[]');
		$('input.keypress', newRow).attr('name', 'keys[]');
		$('input', newRow).prop('disabled', false).focus();
		$(event.target).parents('.options-table').trigger('change');
	});

	$('.menu-applet .action.remove').live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
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
	});

	$('.menu-applet .options-table').live('change', function() {
		var first = $('tbody tr', this).first();
		$('.action.remove', first).hide();
	});

	$('.menu-applet .options-table').trigger('change');
});
