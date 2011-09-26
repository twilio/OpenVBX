<?php

/**
 * Deleting an Application
 * Use the deleteApplication() method to remove an application. This cannot be undone; once an application has been
 * deleted, it cannot be restored without recreating it from scratch.
 * 
 */

require_once '../tropo.class.php';

$userid = "";
$password = "";
$applicationID = "";

$tropo = new Tropo();

try {
	echo $tropo->deleteApplication($userid, $password, $applicationID);
}

catch (TropoException $ex) {
	echo $ex->getMessage();
}

?>