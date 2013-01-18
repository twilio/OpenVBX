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


	$('#dialog-email').dialog({ 
		autoOpen: false,
		width: 350,
		open: function(event, ui) {
			var ajaxUrl = OpenVBX.home + '/devices/send_iphone_guide';
			$.post(ajaxUrl, {});
		},
		buttons: {
			'OK' : function() {
				$(this).dialog('close');
			}
		}
	});


	$('#dialog-number').dialog({ 
		autoOpen: false,
		width: 350,
		buttons: {
			'Add' : function() {
				var ajaxUrl = OpenVBX.home + '/devices/number/add';
				var params = $('#dialog-number').values();

				if($('.device-list').css('display')=='none') {
					params['number[sequence]'] = 0;
				} else {
					params['number[sequence]'] = $('.device-list').length;
				}

				$.post(ajaxUrl, params, function(data) {

					$('#dialog-number .error-message').text(data.message).removeClass('hide');

					if(data.error) {
						$('button').prop('disabled', false);
						return false;
					}

					$('.device-container').removeClass('hide');
					$('.devices-blank').addClass('hide');

					$('#dialog-number input').val('');
					$('button').prop('disabled', false);
					$('#dialog-number .error-message').addClass('hide');

					$('#dialog-number').dialog('close');

					// Add a new row to the device list
					var row = $('.device-list .prototype').clone();

					row.removeClass('prototype hide')
						.addClass('device enabled ui-state-default')
						.attr('rel', data.id);

					$('.device-name', row).text(data.name);
					$('.device-value', row).text(data.value);
					$('.enable-sms', row).prop('checked', data.sms ? true : false);
					$('.device-list .prototype').before(row);

					// Add to the "Record a Voicemail" device selector
					var option = $('<option></option>');
					$('select[name="number"]').append(option.text(data.name).attr('value', data.value));

					$('.device-list .prototype :input').val('');
					$('.device-list').removeClass('hide');
					$('.no-devices').addClass('hide');
					$('.vbx-menu-items-right').removeClass('hide');
					
					if (window.parent.Client) {
						window.parent.Client.ui.refreshDevices();
					}
				}, 'json');
			},
			'Cancel' : function() {
				$(this).dialog('close');
			}
		}
	});

	var openEmailDialog = function () {
		$('#dialog-email').dialog('open');

		return false;
	};

	var openNumberDialog = function () {
		$('#dialog-number').dialog('open');

		return false;
	};

	$('.email-button').live('click', openEmailDialog);

	$('.add-device').live('click', openNumberDialog);

	var updateDevice = function(id, params) {
		$.ajax({
			url: OpenVBX.home + '/devices/number/' + id,
			data: {
				device : params
			},
			success: function(data) {
				if(!data.error)
				{
					if(typeof params.sms != "undefined")
						$.notify('SMS Notifications have been turned ' + (params.sms? 'on' : 'off'));
					if(typeof params.is_active != "undefined")
						$.notify('Device has been turned ' + (params.is_active? 'on' : 'off'));

					return;
				}

				$('.error-dialog').dialog('option', 'buttons', {
					"Ok": function() {
						/* TODO: reset */
						$(this).dialog("close");
					}
				});
				$('.error-dialog .error-code').text('');
				$('.error-dialog .error-message').text('Unable to update device.  Please try again or contact your OpenVBX provider.');

				$('.error-dialog').dialog('open');
			},
			dataType : 'json',
			type: 'POST'
		});
	};

	var toggleEnableDeviceValue = function(event, anchor) {
		event.preventDefault();

		var enableDevice = $('.enable-device', $(anchor).parent());
		enableDevice.prop('checked', !enableDevice.prop('checked')).trigger('change');
	};

	$('.device-status a.on').live('click', function(event) {
		toggleEnableDeviceValue(event, this);
	});
	$('.device-status a.off').live('click', function(event) {
		toggleEnableDeviceValue(event, this);
	});

	$('.enable-sms').live('change', function(event) {
		event.preventDefault();

		updateDevice($(this).parents('.device').attr('rel'),
					 {
						 'sms' : $(event.target).prop('checked')? 1 : 0
					 });
	});


	$('.device-status .enable-device').live('change', function(event) {
		var is_active = $(this).prop('checked') === true? 1 : 0;
		var device_status = $(this).parents('.device-status');

		var activate = '.off';
		var deactivate = '.on';
		if(is_active) {
			var activate = '.on';
			var deactivate = '.off';
		}

		$(activate, device_status)
			.addClass('enabled')
			.removeClass('disabled');
		$(deactivate, device_status)
			.addClass('disabled')
			.removeClass('enabled');

		updateDevice($(this).parents('.device').attr('rel'),
					 {
						 'is_active' : is_active
					 });

	});

	$('.device .edit').live('click', function(event) {
		event.preventDefault();
	});

	$('.device .trash').live('click', function(event) {
		event.preventDefault();
		$('button').prop('disabled', true);
		var id = $(this).closest('.device').attr('rel');
		var deviceValue = $(this).closest('.device').find('.device-value').text()
		var ajaxUrl = 'devices/number/' + id;
		$.ajax({
			url: ajaxUrl,
			data: null,
			success: function(data) {
				if(!data.error)
				{
				    // Remove it from the device list
					$('.device-list .device[rel="' + id + '"]')
						.fadeOut(function() {
							$(this).remove();

							if(!$('.device-list .device').length) {
								$('.device-list').addClass('hide');
								$('.devices-blank').removeClass('hide');
								$('.no-devices').removeClass('hide');
								$('.vbx-menu-items-right').addClass('hide');
							}
						});

					// Remove from the <select> element for the Record dialog
					$('select[name="number"] option[value="' + deviceValue + '"]').remove();

					$('.device-list').removeClass('hide');
					
					if (window.parent.Client) {
						window.parent.Client.ui.refreshDevices();
					}
					
					return $('button').prop('disabled', false);
				}

				$('.error-dialog').dialog('option', 'buttons', {
					"Ok": function() {
						$(this).dialog("close");
					}
				});
				$('.error-dialog .error-code').text('');
				$('.error-dialog .error-message').text('Unable to delete number.  Please try again or contact your OpenVBX provider.');

				$('.error-dialog').dialog('open');
			},
			dataType : 'json',
			type: 'DELETE'
		});
	});

	$('.device-list').sortable({
		placeholder: 'device-placeholder',
		items: 'li:not(.ui-state-disabled)',
		update : function(event, ui) {
			var sorted = $.map($('.device-list li'),
				function(n, i) {
					return $(n).attr('rel');
				});

			$.ajax({
				url: OpenVBX.home + '/devices/number/order',
				data: {
					order : sorted
				},
				success: function(data) {
					if(!data.error)
					{
						return $.notify('Dial order has been updated');
					}

					$('.error-dialog').dialog('option', 'buttons', {
						"Ok": function() {
							$('.device-list').sortable('cancel');
							$(this).dialog("close");
						}
					});
					$('.error-dialog .error-code').text('');
					$('.error-dialog .error-message').text('Unable to set sort order.  It will be reset and you will need to try again later.  It appears to be a network issue communicating with the server.');

					$('.error-dialog').dialog('open');
				},
				dataType : 'json',
				type: 'POST'
			});
		}

	});
	$('.device-list').disableSelection();
	$('.device-list .voicemail.device').addClass('ui-state-disabled');
	$('button.cancel').live('click', function() {
		$('.device-table tbody').sortable('cancel');
	});

	var toggleApplicationContainer = function() {
		$('.application-container').toggle();
		$(this).hasClass('opened-apps') ? $(this).removeClass('opened-apps') : $(this).addClass('opened-apps');
		$(this).hasClass('opened-apps') ? $(this).text('Hide applications') : $(this).text('More for your device');
		return false;
	};

	if(document.location.hash == '#mobile-apps')
		$('.mobile-apps-toggle-link').click();


});
