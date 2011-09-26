<?php

/**
 * Deleting an Address
 * You can use the deleteApplicationAddress() method remove a phone number, IM account or token from an application.
 * 
 */

require_once '../tropo.class.php';

$userid = "";
$password = "";
$applicationID = "";
$number = "";

$tropo = new Tropo();

try {
	echo $tropo->deleteApplicationAddress($userid, $password, $applicationID, AddressType::$number, $number);
}

catch (TropoException $ex) {
	echo $ex->getMessage();
}

?>