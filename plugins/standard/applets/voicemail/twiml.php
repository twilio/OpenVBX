<?php

$response = new TwimlResponse; // start a new Twiml response
if(!empty($_REQUEST['RecordingUrl'])) // if we've got a transcription
{
	$CI =& get_instance();
	// add a voice message 
	OpenVBX::addVoiceMessage(
						 AppletInstance::getUserGroupPickerValue('permissions'),
						 $CI->input->get_post('CallSid'),
						 $CI->input->get_post('From'),
						 $CI->input->get_post('To'), 
						 $CI->input->get_post('RecordingUrl'),
						 $CI->input->get_post('RecordingDuration')
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
