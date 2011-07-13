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

/* Tabify - Simple tabbing plugin with browser history support */
jQuery.fn.tabify = function(options) {
    settings = jQuery.extend({
        selector : '#tab-',
        view : '.vbx-tab-view',
        defaultView : ''
    }, options);

	return $(this).each(function() {
		var tabs = this;
		$('li', this).click(function(e) {
			$('li', tabs).removeClass('selected');
			$(this).addClass('selected');
			var anchor = $('a', this).attr('href').replace(/^.*#/, '');
			$(settings.view).hide();
			$(settings.selector+anchor).show();
			document.location.href = document.location.href.replace(/^.*/,'#'+anchor);
			return true;
		});

		var hash = function() {
			var _hash =  document.location.hash.replace('#','');
			if(_hash == '') {
				_hash = settings.defaultView;
			}
			return _hash;
		};

		$(window).hashchange( function() { $('a[href="#'+hash()+'"]').click(); } );
		$(window).trigger( "hashchange" );
		$('a[href="#'+hash()+'"]').click();
		history.navigationMode = 'compatible';

	});
};