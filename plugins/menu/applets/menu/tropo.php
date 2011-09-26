<?php

$tropo = new Tropo;

try {
	$tropo_session = new Session_Tropo($_COOKIE['tropo_session']);
} catch (Exception $e) {
	$tropo_session = null;
}

try {
	$result = new Result_Tropo();
	$digits = $result->getValue();
	// Set digits to false if no result
	if (!$digits && $digits !== 0)
		$digits = false;
} catch (Exception $e) {
	$digits = false;
}

/* Fetch all the data to operate the menu */
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
			$sayText = AudioSpeechPickerWidget::getJsonForValue($invalid_option, null);
			$tropo->say($sayText);
			$tropo->on(array('event'=>'continue', 'next'=>''));
		}
		else
		{
			$tropo->say('You selected an incorrect option.');
			$tropo->on(array('event'=>'continue', 'next'=>''));
		}
		
		$tropo->renderJSON();
		exit;
	}
}

if(!empty($selected_item))
{
	$tropo->on(array('event'=>'continue', 'next'=>$selected_item));
	$tropo->renderJSON();
	exit;
}

// $gather = $response->addGather(compact('numDigits'));
$sayText = AudioSpeechPickerWidget::getJsonForValue($prompt, null);
// $gather->append($verb);

$finalSay = '<speak>';
$finalSay .= $sayText;

// Infinite loop
if($repeat_count == -1)
{
	// $response->addRedirect();
	$tropo->on(array('event'=>'continue', 'next'=>$_SERVER['REQUEST_URI']));
	// Specified repeat count
}
else
{
	for($i=1; $i < $repeat_count; $i++)
	{
		// $gather->addPause(array('length' => 5));
		// $gather->append($verb);
		$finalSay .= "<break time='5s' />";
		$finalSay .= $sayText;
	}
}

$finalSay .= '</speak>';

$tropo->ask($finalSay, array(
	'choices'=>"[$numDigits DIGITS]",
	'mode' => 'any',
));

// Go to next applet if caller doesn't enter anything after menu
if(!empty($next))
{
	$instanceID = AppletInstance::getInstanceId();

	$ci = &get_instance();
	$data = $ci->session->userdata("menu-$instanceID");

	if ($data && !$result) {
		$tropo->on(array('event'=>'continue', 'next'=>$next));
	} else {
		$ci->session->set_userdata("menu-$instanceID", true);
		$tropo->on(array('event'=>'continue', 'next'=>$_SERVER['REQUEST_URI']));
	}
}
else
{
	$tropo->on(array('event'=>'continue', 'next'=>$_SERVER['REQUEST_URI']));
}

$tropo->renderJSON();
