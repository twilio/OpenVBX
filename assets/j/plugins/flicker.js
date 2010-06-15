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

jQuery.fn.flicker = function(options) {
	var settings = jQuery.extend({
		color : '#FEEEBD',
		speed : 'slow'
	}, options);
	
	var flickerAnimation = function(item) {
		if(item.flickerLocked) {
			return;
		}
		
		item.flickerLocked = true;
		var bgColor = $(item).css('background-color');
		$(item).animate(
			{
				backgroundColor : settings.color,
				queue : true
			}, 
			settings.speed)
			.animate(
				{
					backgroundColor : bgColor,
					queue : true
				},
				settings.speed,
				function() {
					item.flickerLocked = false;
				});
	};
	
	return this.each(function() {
		flickerAnimation(this);

		return $(this);
	});
};