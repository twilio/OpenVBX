<?php

$response = new TwimlResponse; // start a new Twiml response
if(!empty($_REQUEST['RecordingUrl'])) // if we've got a transcription
{
	// add a voice message 
	OpenVBX::addVoiceMessage(
						 AppletInstance::getUserGroupPickerValue('permissions'),
						 $_REQUEST['CallSid'],
						 $_REQUEST['From'],
						 $_REQUEST['To'], 
						 $_REQUEST['RecordingUrl'],
						 $_REQUEST['RecordingDuration']
					 );		
}
else
{
	$permissions = AppletInstance::getUserGroupPickerValue('permissions'); // get the prompt that the user configured
	$isUser = $permissions instanceOf VBX_User? true : false;

	if($isUser)
	{
		$prompt = $permissions->voicemail;
	}
	else
	{
		$prompt = AppletInstance::getAudioSpeechPickerValue('prompt');
	}

	if (!AudioSpeechPickerWidget::setVerbForValue($prompt, $response)) 
	{
		// fallback to default voicemail message
		$response->say('Please leave a message. Press the pound key when you are finished.');
	}

	// add a <Record>, and use VBX's default transcription handler
	$response->record(array(
		'transcribeCallback' => site_url('/twiml/transcribe') 
	));
}

$response->respond(); // send response
