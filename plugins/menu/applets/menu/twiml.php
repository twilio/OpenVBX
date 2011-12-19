<?php

$ci = &get_instance();
$response = new TwimlResponse;

/* Fetch all the data to operate the menu */
$digits = isset($_REQUEST['Digits'])? $ci->input->get_post('Digits') : false;
$prompt = AppletInstance::getAudioSpeechPickerValue('prompt');
$invalid_option = AppletInstance::getAudioSpeechPickerValue('invalid-option');
$repeat_count = AppletInstance::getValue('repeat-count', 3);
$next = AppletInstance::getDropZoneUrl('next');
$selected_item = false;

/* Build Menu Items */
$choices = (array) AppletInstance::getDropZoneUrl('choices[]');
$keys = (array) AppletInstance::getDropZoneValue('keys[]');
$menu_items = AppletInstance::assocKeyValueCombine($keys, $choices);

$numDigits = 1;
foreach($keys as $key)
{
	if(strlen($key) > $numDigits)
	{
		$numDigits = strlen($key);
	}
}

if($digits !== false)
{
	if(!empty($menu_items[$digits]))
	{
		$selected_item = $menu_items[$digits];
	}
	else
	{
		if($invalid_option)
		{
			AudioSpeechPickerWidget::setVerbForValue($invalid_option, $response);
			$response->redirect();
		}
		else
		{			 
			$response->say('You selected an incorrect option.', array(
					'voice' => $ci->vbx_settings->get('voice', $ci->tenant->id),
					'language' => $ci->vbx_settings->get('voice_language', $ci->tenant->id)
				));
			$response->redirect();
		}
		
		$response->respond();
		exit;
	}
		
}

if(!empty($selected_item))
{
	$response->redirect($selected_item);
	$response->respond();
	exit;
}

$gather = $response->gather(compact('numDigits'));
// $verb = AudioSpeechPickerWidget::getVerbForValue($prompt, null);
AudioSpeechPickerWidget::setVerbForValue($prompt, $gather);
// $gather->append($verb);

// Infinite loop
if($repeat_count == -1)
{
	$response->redirect();
	// Specified repeat count
}
else
{
	for($i=1; $i < $repeat_count; $i++)
	{
		$gather->pause(array('length' => 5));
		AudioSpeechPickerWidget::setVerbForValue($prompt, $gather);
		// $gather->append($verb);
	}
}

if(!empty($next))
{
	$response->redirect($next);
}

$response->respond();
