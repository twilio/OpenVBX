<?php
// The response object constructs the TwiML for our applet
$response = new TwimlResponse;

// In order to add the custom greeting the callflow creator typed 
// in we need to get the value of prompt-text. We can do this by 
// using AppletInstance::getValue()
$response->say(AppletInstance::getValue('prompt-text'));

// $primary is getting the url created by what ever applet was put
// into the primary dropzone
$primary = AppletInstance::getDropZoneUrl('primary');

if(!empty($primary)) {
	// As long as the primary dropzone is not empty add the redirect 
	// twiml to $response
	$response->redirect($primary);
}

// This will create the twiml for hellomonkey
$response->respond();

