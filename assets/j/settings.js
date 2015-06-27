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
	
	$('#settings-country-select select').live('change', function() {
		var select = $(this),
			img = $(this).siblings('img'),
			imgpath = '/assets/i/countries/' + select.val().toLowerCase() + '.png';
			img.attr('src', OpenVBX.assets + imgpath);
	});

	function langCodesEnable(select) {
		select.removeClass('hide')
			.show()
			.find('select')
			.prop('disabled', false);
	}

	function langCodesDisable(select) {
		select.addClass('hide')
			.hide()
			.find('select')
			.prop('disabled', true);
	}

	$('#site-voice').live('change', function() {
		var voice = $(this).val(),
			defaultLanguages = $('#lang-code-default'),
			extendedLanguages = $('#lang-code-extended');

		if (voice == 'man' || voice == 'woman') {
			langCodesEnable(defaultLanguages);
			langCodesDisable(extendedLanguages);
		} else {
			langCodesDisable(defaultLanguages);
			langCodesEnable(extendedLanguages);
		}
	}).trigger('change');
});
