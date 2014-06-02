<?php
$ci = &get_instance();

/* Get the body of the SMS message */
$body = isset($_REQUEST['Body'])? trim($ci->input->get_post('Body')) : null;
$body = strtolower($body);

$prompt = AppletInstance::getValue('prompt');
$keys = AppletInstance::getValue('keys[]');
$responses = AppletInstance::getValue('responses[]');
$menu_items = AppletInstance::assocKeyValueCombine($keys, $responses, 'strtolower');

$response = new TwimlResponse;
/* Display the menu item if we found a match - case insensitive */
if(array_key_exists($body, $menu_items) && !empty($menu_items[$body]))
{
	$response_text = $menu_items[$body];
}
else
{
	/* Display the prompt if incorrect */
	$response_text = $prompt;
}

$response->message($response_text);
$response->Respond();