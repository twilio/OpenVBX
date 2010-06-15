<?php

/* Get the body of the SMS message */
$body = isset($_REQUEST['Body'])? trim($_REQUEST['Body']) : null;
$body = strtolower($body);

$prompt = AppletInstance::getValue('prompt');
$keys = AppletInstance::getValue('keys[]');
$responses = AppletInstance::getValue('responses[]');
$menu_items = AppletInstance::assocKeyValueCombine($keys, $responses, 'strtolower');

$response = new Response();
/* Display the menu item if we found a match - case insensitive */
if(array_key_exists($body, $menu_items) && !empty($menu_items[$body]))
{
	$response->addSms($menu_items[$body]);
}
else
{
	/* Display the prompt if incorrect */
	$response->addSms($prompt);
}

$response->Respond();