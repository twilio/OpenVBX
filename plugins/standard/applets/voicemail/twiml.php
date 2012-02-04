<?php
$CI =& get_instance();
$transcribe = (bool) $CI->vbx_settings->get('transcriptions', $CI->tenant->id);

$response = new TwimlResponse; // start a new Twiml response
if(!empty($_REQUEST['RecordingUrl'])) // if we've got a transcription
{
	// add a voice message 
	OpenVBX::addVoiceMessage(
						 AppletInstance::getUserGroupPickerValue('permissions'),
						 $CI->input->get_post('CallSid'),
						 $CI->input->get_post('From'),
						 $CI->input->get_post('To'), 
						 $CI->input->get_post('RecordingUrl'),
						 $CI->input->get_post('RecordingDuration'),
						 ($transcribe == false) // if not transcribing then notify immediately
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
		$response->say('Please leave a message. Press the pound key when you are finished.', array(
				'voice' => $CI->vbx_settings->get('voice', $CI->tenant->id),
				'language' => $CI->vbx_settings->get('voice_language', $CI->tenant->id)
			));
	}

	// add a <Record>, and use VBX's default transcription handler
	$record_params = array(
		'transcribe' => 'false'
	);
	if ($transcribe) {
		$record_params['transcribe'] = 'true';
		$record_params['transcribeCallback'] = site_url('/twiml/transcribe');
	}

	$response->record($record_params);
}

$response->respond(); // send response
