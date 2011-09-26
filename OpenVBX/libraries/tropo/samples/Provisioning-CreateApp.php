<?php

/**
 * Creating a New Application
 * Use the createApplication() method to add a brand new application. 
 * You can define a voice and messaging URL in the Request Body, but this 
 * method won't assign any addresses. You'll need to update the
 * application once it's created to add a phone number or IM account.
 * 
 */

require_once '../tropo.class.php';

$userid = "";
$password = "";

$tropo = new Tropo();

try {
	
	$appSettings = array(
		"name" => "My Awesome App", 
		"voiceUrl" => "http://www.fake.com/index.php", 
		"messagingUrl" => "http://www.fake.com/index2.php", 
		"platform" => "webapi", 
		"partition" => "staging"
	);	
	
	echo $tropo->createApplication($userid, $password, $appSettings);
	
}

catch (TropoException $ex) {
	echo $ex->getMessage();
}

?>