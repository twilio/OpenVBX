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

	/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
	$("#dlg_upgrade").dialog({
		autoOpen: false,
		width: 490,
		buttons: {
			'Upgrade': function() {
				var add_button = $('button', $('#dlg_upgrade').parent()).first();
				var add_button_text = add_button.text();
				add_button.html('Upgrading <img alt="loading" src="'+OpenVBX.assets+'/assets/i/ajax-loader.gif" />');
				$.ajax({
					type: 'POST',
					url: $('#dlg_upgrade form').attr('action'),
					data: $('input', $('#dlg_upgrade form')),
					success: function(data) {
						$('button').prop('disabled', true);
						$('#dlg_upgrade .error-message').slideUp();
						if(data.error) {
							$('#dlg_upgrade .error-message')
								.text(data.message)
								.slideDown();

							$('button').prop('disabled', false);
							return add_button.text(add_button_text);
						}

						document.location.reload(true);
					},
					error: function(xhr, status, error) {
						$('#dlg_upgrade .error-message')
							.text(status + ' :: ' + error)
							.slideDown();
						$('button').prop('disabled', false);
					},
					dataType: 'json'
				});

				return false;
			},
			'Cancel' : function() {
				$('#dlg_upgrade .error-message').hide();
				$(this).dialog('close');
			}
		}
	}).closest('.ui-dialog').addClass('add');

	$('.numberinfo').hover(function() {
		var phone_id = $(this).closest('tr').attr('rel');
		var phone = $.trim($(this).text().replace('(', '').replace(')', '').replace(' ', '').replace('-', ''));

		$(this).append('<div class="infobox"><img alt="loading" src="'+OpenVBX.assets+'/assets/i/ajax-loader.gif" /></div>');
		var numberinfo = $(this).children('.infobox');
		numberinfo.css('position', 'absolute');
		$(document).mousemove(function(e) {
			numberinfo.css('top', e.pageY+'px');
			numberinfo.css('left', e.pageX+'px');
		});
		// Get the number info
		var ajaxUrl = 'numbers/info/' + phone_id + '/' + phone;
		$.getJSON(ajaxUrl, function(data) {
			numberinfo.html('<h1>Info for '+data.number_friendly+'<h1>')
				.append('<p><strong>Provider:</strong> '+data.info.provider+'</p>')
				.append('<p><strong>Number Type:</strong> '+data.info.type+'</p>');
			if (data.info.siblings) {
				var siblingText = '';
				for (var i = data.info.siblings.length - 1; i >= 0; i--) {
					var sibling = data.info.siblings[i];
					siblingText = siblingText + '<li>' + sibling + '</li>';
				};
				numberinfo.append('<div class="siblings"><p><strong>Siblings:</strong></p><ul>'+siblingText+'</ul></div>');
			}
		});
	}, function() {
		var numberinfo = $(this).children('.infobox');
		numberinfo.remove();
	});
	/** End Disruptive Technologies code **/
	
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
			$('tr[rel="'+select_flow.closest('tr').attr('rel')+'"] .incoming-number-flow')
			// select_flow.parents('td')
				.children('p.dropdown')
				.html('<span class="option-selected">' 
					  + $('option:selected', select_flow).text()
					  + '</span>'
					  +'<a class="action flow"><span class="replace">Select</span></a>');
			$("#dlg_change").dialog('open');
		} else {
			$('tr[rel="'+select_flow.closest('tr').attr('rel')+'"] .incoming-number-flow')
			// select_flow.parents('td')
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
					$('tr[rel="'+select_flow.closest('tr').attr('rel')+'"] .incoming-number-flow select[name="flow_id"]').data('old_val', data.id);
					$('tr[rel="'+select_flow.closest('tr').attr('rel')+'"] .incoming-number-flow select[name="flow_id"]').val(data.id);
					// select_flow.data('old_val', data.id);
					$.notify($('.incoming-number-phone', row).text() + ' is now connected to '+$('option:selected', row).text());
					$('.incoming-number-flow', row).children('select, p, span').toggle();
				} else {
					alert('!success');
					if(data.message) $.notify(data.message);
					$('tr[rel="'+select_flow.closest('tr').attr('rel')+'"] .incoming-number-flow select[name="flow_id"]').val(
					select_flow.data('old_val'));
					//select_flow.val(select_flow.data('old_val'));
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
						$('tr[rel="'+select_flow.closest('tr').attr('rel')+'"] .incoming-number-flow select[name="flow_id"]').data('old_val', data.id);
						$('tr[rel="'+select_flow.closest('tr').attr('rel')+'"] .incoming-number-flow select[name="flow_id"]').val(data.id);
						$.notify($('.incoming-number-phone', row).text() + ' is now connected to '+$('option:selected', row).text());
						$('.incoming-number-flow', row).children('select, p, span').toggle();
					} else {
						if(data.message) $.notify(data.message);
						$('tr[rel="'+select_flow.closest('tr').attr('rel')+'"] .incoming-number-flow select[name="flow_id"]').val(select_flow.data('old_val'));
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
			data: $('input[type="text"], select, input[type="radio"]:checked', $('#dlg_add form')),
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

	$('select[name="api_type"]').change(function() {
		if ($(this).val() == 'twilio') {
			// Change country code
			// Twilio only supports US phone numbers
			$('#country-code').html('1');
			$('#twilio-api-type').show();
			$('#tropo-api-type').hide();
		} else {
			// Change country code
			// Tropo supports international phone numbers
			$('#country-code').html('<input type="text" name="country_code" id="iCountryCode" maxlength="3" value="1" />');
			$('#tropo-api-type').show();
			$('#twilio-api-type').hide();
		}
	});

	$('.delete').click(function() {
		$(this).addClass('selected');
		$("#dlg_delete").dialog('open');
		return false;
	});
});
