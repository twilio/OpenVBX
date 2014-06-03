<?php

// The response object constructs the TwiML for our applet
$response = new TwimlResponse;

// Add a say based on what has been entered in on the call flow
$response->say(AppletInstance::getValue('prompt-text'));

// $primary stores the url to the primary action that was selected
$primary = AppletInstance::getDropZoneUrl('primary');

// $fallback stores the url to the fallback action selected
$fallback = AppletInstance::getDropZoneUrl('fallback');

// We're taking the caller's number and the number entered in on the applet
// and normalizing them so we can then compare them
$caller = normalize_phone_to_E164($_REQUEST['Caller']);
$screenNumber = normalize_phone_to_E164(AppletInstance::getValue('key'));

if($caller == $screenNumber) {
	// If the caller's number matches the number that we proceed with the
	// primary action.
	$response->redirect($primary);
}
else {
	// if the caller is unknown, proceed with the fallback action
	$response->redirect($fallback);
}

// This will create the twiml for hellomonkey
$response->respond();