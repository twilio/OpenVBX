<?php

// TODO: Transcriptions

$tropo = new Tropo;
try {
	$session = new Session_Tropo($_COOKIE['tropo_session']);
	$sessionId = $session->getId();
} catch (TropoException $e) {
	$session = null;
	$sessionId = 'session';
}
if(isset($_GET['filename']) && !empty($_GET['filename'])) // if we've got a transcription
{
	if ($session) {
		$fromObj = $session->getFrom();
		$toObj = $session->getTo();
		$from = $fromObj['id'];
		$to = $toObj['id'];
		if (strlen($from) < 11)
			$from = "1$from";
		if (strlen($to) < 11)
			$to = "1$to";
		$from = "+$from";
		$to = "+$to";
	} else {
		$from = "";
		$to = "";
	}
	$duration = isset($_GET['start']) ? time()-$_GET['start'] : '0';
	// add a voice message 
	OpenVBX::addVoiceMessage(
							 AppletInstance::getUserGroupPickerValue('permissions'),
							 $sessionId,
							 $from,
							 $to, 
							 site_url('audio-uploads/'.$_GET['filename']),
							 $duration
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

	$sayText = AudioSpeechPickerWidget::getJsonForValue($prompt, 
		"Please leave a message. Press the pound key when you are finished.");

	// add a <Record>, and use VBX's default transcription handler
	$tropo->record(array(
		'say'=>"<speak>$sayText</speak>",
		'url'=>site_url('/tropojson/transcribe')."?filename=$sessionId.mp3",
		'format'=>'audio/mp3',
		'choices'=>'#'
	));

	$tropo->on(array('event'=>'continue', 
		'next'=>
			$_SERVER['REQUEST_URI']."?filename=$sessionId&start=".time()
	));
}

$tropo->renderJSON();