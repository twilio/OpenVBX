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

jQuery.fn.buttonista = function(options) {
	var settings = jQuery.extend({
		menu : 'ul',
		toggler : '.toggler'
	}, options);
	
	var toggleMenu = function() {
		$(this).parent()
			.children(settings.menu).toggleClass('open');
		if($(this).parent().children(settings.menu).hasClass('open') &&
			typeof settings.focus != 'undefined')
		{
			$(this).parent()
				.children(settings.menu).children(settings.focus).focus()
		}
		return false;
	};
	
	var closeMenu = function(event) {
		if (event.keyCode == '27') {
			$(settings.menu).removeClass('open');
		}
	};

	$(window).keypress( closeMenu );

	return this.each(function() {
		var link = $(this);
		link.click( toggleMenu );

		var toggler = $(settings.toggler, link.parent());
		toggler.click(function(event) {
			event.preventDefault();

			link.click();
		});

	});
};