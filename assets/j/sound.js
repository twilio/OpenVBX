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

function convertMsecsToMinutesAndSeconds (msecs)
{
	var minutes = Math.floor((msecs / 1000 / 60));
	var seconds = Math.floor((msecs / 1000) % 60);
	
	return (minutes < 10 ? "0" : "") + minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
}

$(document).ready(function() {

	// Normally SM2 would load as soon as the JS file loaded, but we commented that part out in
	// soundmanager2.js.  The reason being that we need OpenVBX.home to be set before we can provide
	// the path to the swf file.
	window.soundManager = new SoundManager();
	soundManager.debugMode = false;
	soundManager.consoleOnly = true;
	soundManager.flashVersion = 9;
	soundManager.url = OpenVBX.assets + '/assets/j/soundmanager2/';
	soundManager.onload = function() {
		// Initialize any a/s widget players might already be onscreen.
		$('.audio-choice input[name="show_player_with_url"]').each(function (index, element) {
			var audioChoice = $(element).closest('.audio-choice');
			var url = $(element).val();
			Pickers.audio.showPlayer(audioChoice, 'current', url, false);
		});
	};
	soundManager.beginDelayedInit();
});