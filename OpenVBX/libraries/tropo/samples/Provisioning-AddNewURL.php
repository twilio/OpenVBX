<?php

/**
 * Updating an application to add Voice / Messaging URLs.
 * This will add both a voice and messaging URL to an existing application; these are the URLs 
 *  that power voice calls and SMS/messaging calls for your application. This can be a file 
 *  hosted at hosting.tropo.com or hosted on an external server, depending on the application.
 */


require_once '../tropo.class.php';

$userid = "";
$password = "";
$applicationID = "";

$tropo = new Tropo();

try {
	
	$appSettings = array(
		"name" => "My Awesome App", 
		"voiceUrl" => "http://www.anotherfake.com/index.php", 
		"messagingUrl" => "http://www.anotherfake.com/index2.php", 
		"platform" => "webapi", 
		"partition" => "staging"
	);	
	
	echo $tropo->updateApplicationProperty($userid, $password, $applicationID, $appSettings);
	
}

catch (TropoException $ex) {
	echo $ex->getMessage();
}