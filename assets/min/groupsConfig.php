<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 **/

$jquery = '../j/frameworks/jquery-1.6.2.min.js';
$jquery_ui = '../j/frameworks/jquery-ui-1.8.14.custom.min.js';
$jquery_cookie = '../j/plugins/jquery.cookie.js';
$jquery_validate = '../j/plugins/jquery.validate.js';

$sources = array(
	'css' => array(
		'../c/reset-fonts-grids-2.8.css',
		'../c/global.css',
		'../c/login.css',
		'../c/utility-menu.css',
		'../c/context-menu.css',
		'../c/navigation.css',
		'../c/content.css',
		'../c/forms.css',
		'../c/buttons.css',
		'../c/controls.css',
		'../c/plugin.css',
		'../c/messages.css',
		'../c/devices.css',
		'../c/voicemail.css',
		'../c/admin.css',
		'../c/flows.css',
		'../c/applet.css',
		'../c/jplayer.css',
		'../c/uploadify.css',
		'../c/timePicker.css',
		'../c/client.css',
	),
	'js' => array(
		'../j/soundmanager2/soundmanager2.js',
		$jquery,
		$jquery_ui,
		'../j/swfupload/swfupload.js',
		'../j/swfupload/swfupload.cookies.js',
		'../j/modal-tabs.js',
		$jquery_cookie,
		'../j/plugins/json.js',
		$jquery_validate,
		'../j/plugins/call-and-sms-dialogs.js',
		'../j/plugins/flicker.js',
		'../j/plugins/jquery.ba-hashchange.js',
		'../j/plugins/jquery.livequery.js',
		'../j/plugins/buttonista.js',
		'../j/plugins/jquery.animateToClass.js',
		'../j/plugins/static.js',
		'../j/plugins/jquery.swfupload.js',
		'../j/plugins/jquery.tabify.js',
		'../j/plugins/jquery.timePicker.min.js',
		'../j/global.js',
		'../j/sound.js',
		'../j/pickers.js',
		'../j/messages.js',
	),
	'iframejs' => array(
		$jquery, 
		$jquery_ui, 
		$jquery_cookie,
		'../j/iframe.js',
		'../j/client.js'	
	),
	'installjs' => array(
		$jquery,
		$jquery_cookie,
		$jquery_validate,
		'../j/steps.js',
		'../j/install.js'
	),
	'upgradejs' => array(
		$jquery,
		$jquery_cookie,
		$jquery_validate,
		'../j/steps.js',
		'../j/upgrade.js'		
	),
	'loginjs' => array(
		$jquery,
		$jquery_ui,
		$jquery_cookie,
		'../j/plugins/json.js',
		$jquery_validate,
		'../j/global.js'
	)
);

$extra_sources = '../../OpenVBX/config/asset-sources.php';
if (is_file($extra_sources))
{
	@include_once($extra_sources);
}

return $sources;