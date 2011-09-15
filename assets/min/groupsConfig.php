<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 **/

$sources = array(
	'css' => array(
		'//assets/c/reset-fonts-grids-2.8.css',
		'//assets/c/global.css',
		'//assets/c/login.css',
		'//assets/c/utility-menu.css',
		'//assets/c/context-menu.css',
		'//assets/c/navigation.css',
		'//assets/c/content.css',
		'//assets/c/forms.css',
		'//assets/c/buttons.css',
		'//assets/c/controls.css',
		'//assets/c/plugin.css',
		'//assets/c/messages.css',
		'//assets/c/devices.css',
		'//assets/c/voicemail.css',
		'//assets/c/admin.css',
		'//assets/c/flows.css',
		'//assets/c/applet.css',
		'//assets/c/jplayer.css',
		'//assets/c/uploadify.css',
		'//assets/c/timePicker.css',
		'//assets/c/client.css',
	),
	'js' => array(
		'//assets/j/soundmanager2/soundmanager2.js',
		'//assets/j/frameworks/jquery-1.6.2.min.js',
		'//assets/j/frameworks/jquery-ui-1.8.14.custom.min.js',
		'//assets/j/swfupload/swfupload.js',
		'//assets/j/swfupload/swfupload.cookies.js',
		'//assets/j/modal-tabs.js',
		'//assets/j/plugins/jquery.cookie.js',
		'//assets/j/plugins/json.js',
		'//assets/j/plugins/jquery.validate.js',
		'//assets/j/plugins/call-and-sms-dialogs.js',
		'//assets/j/plugins/flicker.js',
		'//assets/j/plugins/jquery.ba-hashchange.js',
		'//assets/j/plugins/jquery.livequery.js',
		'//assets/j/plugins/buttonista.js',
		'//assets/j/plugins/jquery.animateToClass.js',
		'//assets/j/plugins/static.js',
		'//assets/j/plugins/jquery.swfupload.js',
		'//assets/j/plugins/jquery.tabify.js',
		'//assets/j/plugins/jquery.timePicker.min.js',
		'//assets/j/global.js',
		'//assets/j/sound.js',
		'//assets/j/pickers.js',
		'//assets/j/messages.js',
		'//assets/j/update-check.js'
	),
	'iframejs' => array(
		'//assets/j/frameworks/jquery-1.6.2.min.js', 
		'//assets/j/frameworks/jquery-ui-1.8.14.custom.min.js', 
		'//assets/j/plugins/jquery.cookie.js',
		'//assets/j/iframe.js',
		'//assets/j/client.js'		
	),
	'flowbuilderjs' => array(
		
	)
);

$extra_sources = '../../OpenVBX/config/asset-sources.php';
if (is_file($extra_sources))
{
	@include_once($extra_sources);
}

return $sources;