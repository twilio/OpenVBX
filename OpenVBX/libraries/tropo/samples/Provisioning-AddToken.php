<?php

/**
 * Updating an Application to Add a Voice Token
 * The updateApplicationAddress() method can be used to add a voice token to your application; you can add a messaging token just by
 * changing the channel to "messaging" instead of "voice".
 * 
 */

require_once '../tropo.class.php';

$userid = "";
$password = "";
$applicationID = "";

$tropo = new Tropo();

try {
	echo $tropo->updateApplicationAddress($userid, $password, $applicationID, array("type" => AddressType::$token, "channel" => "messaging"));
}

catch (TropoException $ex) {
	echo $ex->getMessage();
}

?>