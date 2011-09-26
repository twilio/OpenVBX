<?php

$tropo = new Tropo;

try {
	$tropo_session = new Session_Tropo($_COOKIE['tropo_session']);
} catch (Exception $e) {
	$tropo_session = null;
}

/* Get the body of the SMS message */
if ($tropo_session) {
	$body = strtolower($tropo_session->getInitialText());
} else {
	$body = '';
}

$prompt = AppletInstance::getValue('prompt');
$keys = AppletInstance::getValue('keys[]');
$responses = AppletInstance::getValue('responses[]');
$menu_items = AppletInstance::assocKeyValueCombine($keys, $responses, 'strtolower');

/* Display the menu item if we found a match - case insensitive */
if(array_key_exists($body, $menu_items) && !empty($menu_items[$body]))
{
	$tropo->say($menu_items[$body]);
}
else
{
	/* Display the prompt if incorrect */
	$tropo->say($prompt);
}

$tropo->renderJSON();