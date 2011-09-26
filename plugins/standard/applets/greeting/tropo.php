<?php

$tropo = new Tropo;

$next = AppletInstance::getDropZoneUrl('next');
$prompt = AppletInstance::getAudioSpeechPickerValue('prompt');

$sayText = AudioSpeechPickerWidget::getJsonForValue($prompt, null);

//$response->append(AudioSpeechPickerWidget::getVerbForValue($prompt, null));
// Audio files
$tropo->say("<speak>$sayText</speak>");

if(!empty($next))
{
	$tropo->on(array('event'=>'continue', 'next'=>$next));    
}

$tropo->renderJSON();