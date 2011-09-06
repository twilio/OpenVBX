<?php

/**
 * Hurk!
 * To control testing we mock the custom Model objects provided by OpenVBX.
 */

// common dependencies
require_once('../../system/libraries/Model.php');
require_once('../libraries/MY_Model.php');

// Über simple loader
foreach (glob(dirname(__FILE__).'/*.php') as $filename) {
	require_once($filename);
}
