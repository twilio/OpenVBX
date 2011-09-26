<?php

/**
 * Updating an Application to Add a Number from the Pool
 * the updateApplicationAddress() method can be used to add a number from the pool of available Tropo numbers, 
 * based on a specified prefix.
 * 
 */

require_once '../tropo.class.php';

$userid = "";
$password = "";
$applicationID = "";

$tropo = new Tropo();

try {
	
	$params  = array("type" => AddressType::$number, "prefix" => "1407");
	echo $tropo->updateApplicationAddress($userid, $password, $applicationID, $params);

}

catch (TropoException $ex) {
	echo $ex->getMessage();
}

?>