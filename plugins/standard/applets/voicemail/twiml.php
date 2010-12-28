<?php

$response = new Response(); // start a new Twiml response
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

	$verb = AudioSpeechPickerWidget::getVerbForValue($prompt, new Say("Please leave a message."));
	$response->append($verb);

	// add a <Record>, and use VBX's default transcription handler
	$response->addRecord(array('transcribeCallback' => site_url('/twiml/transcribe') ));
}

$response->Respond(); // send response
