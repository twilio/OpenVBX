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
	/* VBX Content Tabs */
	$('.vbx-content-tabs').each(function() {
		var tabs = this;
		$('li', this).click(function(e) {
			$('li', tabs).removeClass('selected');

			$(this).addClass('selected');

			var anchor = $('a', this).attr('href').replace(/^.*#/, '');

			$('.vbx-tab-view').hide();
			$('#settings-'+anchor).show();

			document.location.href = document.location.href.replace(/^.*/,'#'+anchor);
			return true;
		});

		var hash = function() {
			var _hash =  document.location.hash.replace('#','');
			if(_hash == '') {
				_hash = "theme";
			}
			return _hash;
		};

		$(window).hashchange( function() { $('a[href="#'+hash()+'"]').click(); } );
		$(window).trigger( "hashchange" );
		$('a[href="#'+hash()+'"]').click();
		history.navigationMode = 'compatible';

	});
	
	dialogs['deleteTenant'] = $('#dDeleteTenant').dialog({ 
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
							$.notify('Tenant has been deleted');
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
	
	dialogs['deleteTenant'].closest('.ui-dialog').addClass('display');
	
	$('a.trash').click(function(event) {
		event.preventDefault();
		activeAnchor = this;
		dialogs['deleteTenant'].dialog('open');
	});
	
});
