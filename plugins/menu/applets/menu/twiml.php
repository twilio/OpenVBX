<?php
$response = new Response();

/* Fetch all the data to operate the menu */
$digits = isset($_REQUEST['Digits'])? $_REQUEST['Digits'] : false;
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
			$verb = AudioSpeechPickerWidget::getVerbForValue($invalid_option, null);
			$response->append($verb);
			$response->addRedirect();
		}
		else
		{			 
			$response->addSay('You selected an incorrect option.');
			$response->addRedirect();
		}
		
		$response->Respond();
		exit;
	}
		
}

if(!empty($selected_item))
{
	$response->addRedirect($selected_item);
	$response->Respond();
	exit;
}

$gather = $response->addGather(compact('numDigits'));
$verb = AudioSpeechPickerWidget::getVerbForValue($prompt, null);
$gather->append($verb);

// Infinite loop
if($repeat_count == -1)
{
	$response->addRedirect();
	// Specified repeat count
}
else
{
	for($i=1; $i < $repeat_count; $i++)
	{
		$gather->addPause(array('length' => 5));
		$gather->append($verb);
	}
}

if(!empty($next))
{
	$response->addRedirect($next);
}

$response->Respond();
