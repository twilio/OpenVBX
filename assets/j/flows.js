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

var dialogs = {};
var activeAnchor;

$(document).ready(function() {
	dialogs['add'] = $('#dAddFlow').dialog({ 
		autoOpen: false,
		width: 340,
		buttons: {
			'OK': function() { 
				$('button', this).prop('disabled', true); 
				$.ajax({
					url : $('#dAddFlow form').attr('action'),
					data : {
						name : $('#dAddFlow input[name="name"]').val()
					},
					success : function(data) {
						if(!data.error) {
							document.location = data.url;
							$('#dAddFlow .error').hide();
							return $('#dAddFlow').dialog('close'); 
						}
						$('#dAddFlow .error').text(data.message).show();
					},
					type : 'POST',
					dataType: 'json'
				});
			},
			Cancel: function() { 
				$(this).dialog('close'); 
			}
		}
	});
	
	dialogs['delete'] = $('#dDeleteFlow').dialog({ 
		autoOpen: false,
		width: 480,
		buttons: {
			'Delete': function() {
				var dialog = this;
				$.ajax({
					url : $(activeAnchor).attr('href'),
					type : 'DELETE',
					success : function(data) {
						if(!data.error) {
							$.notify('Flow has been deleted');
							$(activeAnchor)
								.parents('tr')
								.fadeOut('fast');
							$(dialog).dialog('close');
							return;
						}
					},
					dataType : 'json'
				});
			},
			Cancel: function() { $(this).dialog('close'); }
		}
	});

	dialogs['copy'] = $('#dCopyFlow').dialog({ 
		autoOpen: false,
		width: 640,
		buttons: {
			'OK': function() { 
				$('form', this).submit(); 
			},
			Cancel: function() { $(this).dialog('close'); }
		}
	});
	
	dialogs['add'].closest('.ui-dialog').addClass('add');
	dialogs['copy'].closest('.ui-dialog').addClass('manage');
	dialogs['delete'].closest('.ui-dialog').addClass('display');
	

	$('.add-flow').click(function(event) {
		event.preventDefault();
		dialogs['add'].dialog('open');
	});

	$('a.trash').click(function(event) {
		event.preventDefault();
		activeAnchor = this;
		dialogs['delete'].dialog('open');
	});

	$('a.copy').click(function(event) {
		event.preventDefault();
		var thisRow = $(this).closest('tr');
		var thisName = $('.col_0', thisRow).text();

		dialogs['copy'].dialog('open');
		$('form', dialogs['copy']).attr('action', this.href);
		$(':text', dialogs['copy']).focus().val(thisName + ' copy');
	});
	
	// edit flow name
	$('.flow-name-display').live('click', function(event) {
		event.stopPropagation();
		$(this).hide()
			.siblings('.flow-name-edit').show();
	});
	$('.flow-name-edit-cancel').live('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		var $this = $(this);
		var $inp = $this.siblings('input[name="flow_name"]');
		$inp.val($inp.attr('data-orig-value'))
			.closest('span').hide()
			.siblings('.flow-name-display').show();
	});
	$('.flow-name-edit button.submit-button').live('click', function(event) {
		event.stopPropagation();
		event.preventDefault();
		var $this = $(this);
		$this.prop('disabled', true);
		var _name = $this.siblings('input[name="flow_name"]').val();
		$this.addClass('disabled');
		$.post(OpenVBX.home + $this.attr('data-action'),
			{
				name: _name
			},
			function(data) {
				$this.removeClass('disabled');
				if (!data.error) {
					$.notify('Flow name has been updated.');
					$('tr#flow-' + data.flow_id).find('input[name="flow_name"]').attr('data-orig-value', _name)
						.closest('span').hide()
						.siblings('.flow-name-display').text(_name).show();
				}
				else {
					$.notify('There was an error updating the Flow: ' + data.message);
				}
				$this.prop('disabled', false);
			},
			'json'
		);
	});
});