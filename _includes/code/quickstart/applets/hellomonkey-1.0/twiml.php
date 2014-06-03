<?php

// The response object constructs the TwiML for our applet
$response = new TwimlResponse;

// The addSay converts writes the twiml to convert text to speech
$response->say('Hello Monkey!');

// $primary is getting the url created by what ever applet was put 
// into the primary dropzone
$primary = AppletInstance::getDropZoneUrl('primary');

// As long as the primary dropzone is not empty add the redirect 
// twiml to $response
if(!empty($primary)) {
	$response->redirect($primary);
}

// This will create the twiml for hellomonkey
$response->respond();