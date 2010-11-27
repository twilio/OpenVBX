<?php

$response = new Response();

$next = AppletInstance::getDropZoneUrl('next');
$prompt = AppletInstance::getAudioSpeechPickerValue('prompt');

$response->append(AudioSpeechPickerWidget::getVerbForValue($prompt, null));
	
if(!empty($next))
{
	$response->addRedirect($next);    
}

$response->Respond();