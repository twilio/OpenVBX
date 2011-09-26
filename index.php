<?php
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

if (file_exists('development.php'))
	include('development.php');

$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if($script_dir == '/') $script_dir = '';

define('WEB_ROOT', $script_dir . '/');
define('ASSET_ROOT', $script_dir . '/assets');

unset($script_dir);

register_shutdown_function("shutdown");
function shutdown()
{
	if(function_exists('get_instance') && !headers_sent())
	{
		$ci = &get_instance();
		if(is_object($ci)
		   && isset($ci->session)
		   && is_object($ci->session))
			$ci->session->persist();
	}
}
/*
|---------------------------------------------------------------
| PHP ERROR REPORTING LEVEL
|---------------------------------------------------------------
|
| By default CI runs with error reporting set to ALL.  For security
| reasons you are encouraged to change this when your site goes live.
| For more info visit:	http://www.php.net/error_reporting
|
*/
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
if (!defined('DEBUG'))
{
	ini_set('display_errors', 'Off');
	ini_set('log_errors', 'On');
}
else
{
	ini_set('display_errors', 'On');
	ini_set('log_errors', 'On');
}

/*
 |---------------------------------------------------------------
 | SYSTEM FOLDER NAME
 |---------------------------------------------------------------
 |
 | This variable must contain the name of your "system" folder.
 | Include the path if the folder is not in the same	 directory
 | as this file.
 |
 | NO TRAILING SLASH!
 |
*/
$system_folder = "system";

/*
 |---------------------------------------------------------------
 | APPLICATION FOLDER NAME
 |---------------------------------------------------------------
 |
 | If you want this front controller to use a different "application"
 | folder then the default one you can set its name here. The folder
 | can also be renamed or relocated anywhere on your server.
 | For more info please see the user guide:
 | http://codeigniter.com/user_guide/general/managing_apps.html
 |
 |
 | NO TRAILING SLASH!
 |
*/
$application_folder = dirname(__FILE__) . '/OpenVBX';
date_default_timezone_set('UTC');

/*
 |===============================================================
 | END OF USER CONFIGURABLE SETTINGS
 |===============================================================
*/


/*
 |---------------------------------------------------------------
 | SET THE SERVER PATH
 |---------------------------------------------------------------
 |
 | Let's attempt to determine the full-server path to the "system"
 | folder in order to reduce the possibility of path problems.
 | Note: We only attempt this if the user hasn't specified a
 | full server path.
 |
*/
if (strpos($system_folder, '/') === FALSE)
{
	if (function_exists('realpath') AND @realpath(dirname(__FILE__)) !== FALSE)
	{
		$system_folder = realpath(dirname(__FILE__)).'/'.$system_folder;
	}
}
else
{
	// Swap directory separators to Unix style for consistency
	$system_folder = str_replace("\\", "/", $system_folder);
}

/*
 |---------------------------------------------------------------
 | DEFINE APPLICATION CONSTANTS
 |---------------------------------------------------------------
 |
 | EXT		- The file extension.  Typically ".php"
 | FCPATH	- The full server path to THIS file
 | SELF		- The name of THIS file (typically "index.php")
 | BASEPATH	- The full server path to the "system" folder
 | APPPATH	- The full server path to the "application" folder
 |
*/
define('EXT', '.'.pathinfo(__FILE__, PATHINFO_EXTENSION));
define('FCPATH', __FILE__);
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH', $system_folder.'/');

if (is_dir($application_folder))
{
	define('APPPATH', $application_folder.'/');
}
else
{
	if ($application_folder == '')
	{
		$application_folder = 'application';
	}

	define('APPPATH', BASEPATH.$application_folder.'/');
}

/*
 |---------------------------------------------------------------
 | LOAD THE FRONT CONTROLLER
 |---------------------------------------------------------------
 |
 | And away we go...
 |
*/
require_once BASEPATH.'codeigniter/CodeIgniter'.EXT;

/* End of file index.php */
/* Location: ./index.php */
