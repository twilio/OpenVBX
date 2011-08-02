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

$(document).ready(function() {
	var select_flow = $('select[name="flow_id"]').hide();
	select_flow.after('<span class="hide cancel"><a class="action close"><span class="replace">Cancel</span></a></span>');
	select_flow.each(function() {
		var flow = $(this);
		flow.parent()
			.children('.cancel')
			.click(function() {
				flow.parents('td').children('select, p, span').toggle();
			});

		flow.parent()
			.append('<p class="dropdown"><span class="option-selected">'+$('option:selected', flow).text()+'</span><a class="action flow"><span class="replace">Select</span></a></p>')
			.children('p.dropdown')
			.click(function() {
				flow.parents('td').children('select, p, span').toggle();
				$(this).hide();
			});
	});
	
	$('button.add').click(function() {
		$('#dlg_add').dialog('open');
	});
	
	$('select[name="flow_id"]').change(function(e) {
		e.preventDefault();
		select_flow = $(this);
		
		if(select_flow.val() == 'new') {
			attach_new_flow(select_flow.closest('tr').attr('rel'));
			return;
		}
		
		/* Revert if empty value */
		if(select_flow.val().length < 1) {
			var value = select_flow.data('old_val');
			$('option:selected', select_flow).prop('selected', false);
			$('option[value="'+value+'"]', select_flow).prop('selected', true);
			return;
		}

		if(select_flow.data('old_val') != select_flow.val()
		   && select_flow.val() > 0
		   && select_flow.data('old_val').length > 0) {
			select_flow.parents('td')
				.children('p.dropdown')
				.html('<span class="option-selected">' 
					  + $('option:selected', select_flow).text()
					  + '</span>'
					  +'<a class="action flow"><span class="replace">Select</span></a>');
			$("#dlg_change").dialog('open');
		} else {
			select_flow.parents('td')
				.children('p.dropdown')
				.html('<span class="option-selected">' 
					  + $('option:selected', select_flow).text()
					  + '</span>'
					  +'<a class="action flow"><span class="replace">Select</span></a>');
			var row = select_flow.closest('tr');
			var pn = row.attr('rel');

			var ajaxUrl = 'numbers/change/' + pn + '/' + select_flow.val();
			$.getJSON(ajaxUrl, function(data) {
				if(data.success) {
					$('option[value="0"]', select_flow).remove();
					select_flow.data('old_val', data.id);
					$.notify($('.incoming-number-phone', row).text() + ' is now connected to '+$('option:selected', row).text());
					$('.incoming-number-flow', row).children('select, p, span').toggle();
				} else {
					if(data.message) $.notify(data.message);
					select_flow.val(select_flow.data('old_val'));
				}
			});
		}
	}).each(function(){
		$(this).data('old_val', $('option:selected',this).attr('value'));
	});

	$("#dlg_change").dialog({ 
		autoOpen: false,
		width: 640,
		buttons: {
			'OK': function() {
				$('button').prop('disabled', true);
				var row = select_flow.closest('tr');
				var pn = row.attr('rel');
				var ajaxUrl = 'numbers/change/' + pn + '/' + select_flow.val();
				$.getJSON(ajaxUrl, function(data) {
					if(data.success) {
						$('option[value="0"]', select_flow).remove();
						select_flow.data('old_val', data.id);
						$.notify($('.incoming-number-phone', row).text() + ' is now connected to '+$('option:selected', row).text());
						$('.incoming-number-flow', row).children('select, p, span').toggle();
					} else {
						if(data.message) $.notify(data.message);
						select_flow.val(select_flow.data('old_val'));
					}
					$('button').prop('disabled', false);
				});
				$(this).dialog('close');
			},
			'Cancel': function() {
				select_flow.val(select_flow.data('old_val'));
				$(this).dialog('close');
			}
		}
	}).closest('.ui-dialog').addClass('add');

	var attach_new_flow = function(number_id) {
		$.ajax({
			url: OpenVBX.home + '/flows',
			success: function(data) {
				var flow_id = data.id;
				var flow_url = data.url;
				$.ajax({
					url: OpenVBX.home + '/numbers/change/' + number_id + '/' + flow_id,
					success : function(data) {
						document.location = flow_url;
					},
					type: 'POST'
				});
			},
			type: 'POST'
		});
	};

	var add_number = function() {			
		var add_button = $('button', $('#dlg_add').parent()).first();
		var add_button_text = add_button.text();
		add_button.html('Ordering <img alt="loading" src="'+OpenVBX.assets+'/assets/i/ajax-loader.gif" />');
		$.ajax({
			type: 'POST',
			url: $('#dlg_add form').attr('action'),
			data: $('input[type="text"], input[type="radio"]:checked', $('#dlg_add form')),
			success: function(data) {
				$('button').prop('disabled', true);
				$('#dlg_add .error-message').slideUp();
				if(data.error) {
					$('#dlg_add .error-message')
						.text(data.message)
						.slideDown();

					$('button').prop('disabled', false);
					return add_button.text(add_button_text);
				}

				$('.ui-dialog-buttonpane button').remove();
				var number_id = data.number.id;
				var setup_button = $('#completed-order .setup');
				setup_button.unbind('click')
					.prop('disabled', false)
					.live('click', function(e) {
						setup_button.append('<img alt="loading" src="'+OpenVBX.assets+'/assets/i/ajax-loader.gif" />');
						e.preventDefault();
						attach_new_flow(number_id);
					});
				
				$('.number-order-interface').slideUp('ease-out', function() { 
					$('#completed-order .number').text(data.number.phone);
					$('#completed-order').slideDown('ease-in');
					add_button.text(add_button_text);
				});

				var number_id = data.number.id;


				$('#completed-order').removeClass('hide');
			},
			error: function(xhr, status, error) {
				$('#dlg_add .error-message')
					.text(status + ' :: ' + error)
					.slideDown();
				$('button').prop('disabled', false);
			},
			dataType: 'json'
		});

		return false;
	};

	$("#dlg_add form").submit(add_number);

	$("#dlg_add").dialog({ 
		autoOpen: false,
		width: 490,
		buttons: {
			'Add number': add_number,
			'Cancel' : function() {
				$('#dlg_add .error-message').hide();
				$(this).dialog('close');
			}
		}
	}).closest('.ui-dialog').addClass('add');

	$("#dlg_delete").dialog({ 
		autoOpen: false,
		width: 640,
		buttons: {
			'Yes': function() {
				var href = $('.delete.selected').attr('href');
				$.ajax({
					type: 'POST',
					url: href,
					data: {'confirmed' : true},
					success: function(data, status) {
						if(data.error)	{
							$.notify(data.message);
							$('#dlg_delete .error-message').text(data.message).show();
						} else {
							$('.vbx-items-grid tr:has(.delete.selected)')
								.fadeOut('fast', function() {
									$(this).removeClass('.selected');
									$.notify('Number has been removed from your account.');
								});
							$('#dlg_delete').dialog('close');
						}
					},
					error: function(xhr, status, error) {
						$('#dlg_delete .error-message')
							.text(status + ' :: ' + error).show();
					},
					dataType: 'json'
				});
				
			},
			'No': function() {
				$(this).dialog('close');
				$('.delete').removeClass('selected');
			}
		}
	}).closest('.ui-dialog').addClass('add');
	
	$(':radio[name="type"]').click(function(){
		if($(this).val() == 'local') {
			$('#pAreaCode').slideDown(function() {
				$('#iAreaCode').focus();
			});
		} else {
			$('#pAreaCode').slideUp();
		}
	});
	
	$('.delete').click(function() {
		$(this).addClass('selected');
		$("#dlg_delete").dialog('open');
		return false;
	});
});
