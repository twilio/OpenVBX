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

;(function($, window, document) {
	$('.vbx-table-section-header .toggle-link').live('click', function(e) {
		e.preventDefault();
		
		var _this = $(this),
			target = $('#vbx-other-numbers'),
			find = '.hide',
			method = 'slideDown',
			speed = 'normal';
			
		if (target.is(':visible')) {
			find = '.show';
			method = 'slideUp';
			speed = 'fast';
		}

		_this.find(find).show().siblings().hide();
		$.fn[method].call(target, [speed]);
	});
	
	$.fn.countrySelect = function(options) {
		return this.each(function() {
			$(this).bind('change', function() {
				var	input = $(this),
					code = $(this).val(),
					country = OpenVBX.countries[code],
					acinput = $('#iAreaCode'),
					imageurl = '/assets/i/countries/' + code.toLowerCase() + '.png';
					
				input.siblings('img').attr('src', OpenVBX.assets + imageurl);
				
				// hide invalid options for this country
				$('.number-order-options .number-type-select').each(function() {
					var inputwrapper = $(this),
						type = inputwrapper.attr('id').replace('number-order-', ''),
						ac_input = '';
					if ($.inArray(type, country.available) > -1) {
						inputwrapper.removeClass('disabled').show();
					}
					else {
						inputwrapper.addClass('disabled').hide();
					}
				});
				
				// swap out the proper regional prefix
				var acparts = country.search.split('*');
				$('#area-code-wrapper').html('')
					.append(acparts[0])
					.append(acinput)
					.append(acparts[1]);
				
				// if an invalid option is selected, select the first valid option
				if (!$('.number-type-select input[type="radio"]:checked').is(':visible')) {
					$('.number-type-select:not(.disabled):first input[type="radio"]')
						.trigger('click');
				}
			}).trigger('change');
		});
	};

	$('.incoming-number-details-toggle').live('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var _this = $(this),
			target = $('#other-details-' + _this.attr('href').replace('#', ''));

		$('#dlg_details')
			.find('.details').html(target.html())
			.end().dialog('open');
	});
	
	$('#dlg_details').dialog({ 
		autoOpen: false,
		width: 490,
		buttons: {
			'Close' : function() {
				$('#dlg_add .error-message').hide();
				$(this).dialog('close');
			}
		}
	});
	
	var select_flow_cancel = '<span class="hide cancel"><a class="action close">' + 
						'<span class="replace">Cancel</span></a></span>';
	var select_flow = $('select[name="flow_id"]')
		.hide() 
		.after(select_flow_cancel)
		.each(function() {
			var flow = $(this);
			flow.parent()
				.children('.cancel')
				.click(function() {
					flow.parents('td').children('select, p, span').toggle();
				})
				.end()
				.append('<p class="dropdown"><span class="option-selected">' +
					$('option:selected', flow).text() +
					'</span><a class="action flow"><span class="replace">Select</span></a></p>')
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
		
		// Revert if empty value or a placeholder option
		if(select_flow.val().length < 1 || select_flow.val().match(/^-/m)) {
			reset_flow_selection(select_flow);
			return;
		}
		
		number_type = select_flow.closest('table').attr('data-type');
		
		if(select_flow.val() == 'new') {
			attach_new_flow(select_flow.closest('tr').attr('rel'));
			return;
		}

		var new_val = select_flow.val(),
			old_val = select_flow.data('old_val');

		if (number_type != 'available') {
			select_flow.parents('td')
				.children('p.dropdown')
				.html('<span class="option-selected">' 
					  + $('option:selected', select_flow).text()
					  + '</span>'
					  +'<a class="action flow"><span class="replace">Select</span></a>');
			$("#dlg_change").dialog('open');
		} 
		else {
			update_number_flow(select_flow);
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
				update_number_flow(select_flow, $('button'));
				$(this).dialog('close');
			},
			'Cancel': function() {
				select_flow.val(select_flow.data('old_val'));
				$(this).dialog('close');
			}
		}
	}).closest('.ui-dialog').addClass('add');

	var update_number_flow = function(select_flow, button) {
		var row = select_flow.closest('tr'),
			pn = row.attr('rel'),
			new_val = select_flow.val(),
			number_type = row.attr('data-type'),
			ajaxUrl = 'numbers/change/' + pn + '/' + new_val;

		row.children('p.dropdown')
			.html('<span class="option-selected">' 
				  + $('option:selected', select_flow).text()
				  + '</span>'
				  +'<a class="action flow"><span class="replace">Select</span></a>');

		$.getJSON(ajaxUrl, function(data) {
			if(data.success) {
				$('option[value="-"]', select_flow).remove();
				select_flow.data('old_val', data.id);
				select_flow.closest('tr')
					.find('.incoming-number-phone')
					.find('.incoming-number-details-toggle, .incoming-number-other-detail, br')
					.remove();
				$.notify($('.incoming-number-phone', row).text() + 
							' is now connected to '+$('option:selected', row).text());
				$('.incoming-number-flow', row)
					.children('select, p, span').toggle();
				if (number_type != 'incoming')
				{
					var parent_table = select_flow.closest('table');
					select_flow.closest('tr').appendTo($('#vbx-incoming-numbers table'));
					// cleanup
					$('#vbx-incoming-numbers table tr.null-row').remove();
					if (parent_table.find('tr').size() < 1)
					{
						parent_table.closest('vbx-numbers-section').remove();
					}
				}
			} else {
				if(data.message) {
					$.notify(data.message);
				}
				select_flow.val(select_flow.data('old_val'));
			}
			if (button) {
				button.prop('disabled', false);
			}
		});
	};

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

	var reset_flow_selection = function(flow) {
		var value = flow.data('old_val');
		$('option[value="' + value + '"]', flow).prop('selected', true)
			.siblings().prop('selected', false);
		return;
	};

	var add_number = function() {			
		var add_button = $('button', $('#dlg_add').parent()).first();
		var add_button_text = add_button.text();
		add_button.html('Ordering <img alt="loading" src="' + 
							OpenVBX.assets + '/assets/i/ajax-loader.gif" />');
		$.ajax({
			type: 'POST',
			url: $('#dlg_add form').attr('action'),
			data: $('input[type="text"], input[type="radio"]:checked, select', $('#dlg_add form')),
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
						setup_button.append('<img alt="loading" src="' + 
											OpenVBX.assets + '/assets/i/ajax-loader.gif" />');
						e.preventDefault();
						attach_new_flow(number_id);
					});
				
				$('.number-order-interface').slideUp('ease-out', function() { 
					$('#completed-order .number').text(data.number.phone);
					$('#completed-order').slideDown('ease-in');
					add_button.text(add_button_text);
				});

				number_id = data.number.id;

				if (window.parent.Client) {
					window.parent.Client.ui.refreshNumbers();
				}
				$('#completed-order').removeClass('hide');
				return true;
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
	
	$('#iCountry').countrySelect();
})(jQuery, window, document);