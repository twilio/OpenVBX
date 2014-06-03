<?php
$response = new TwimlResponse;

$choices = AppletInstance::getDropZoneUrl('choices[]');
$keys = AppletInstance::getValue('keys[]');

foreach($keys AS $i=> $key) {
	$keys[$i] = normalize_phone_to_E164($key);
}

$menu_items = AppletInstance::assocKeyValueCombine($keys, $choices);
$fallback = AppletInstance::getDropZoneUrl('fallback');
$text = AppletInstance::getValue('prompt-text');
$caller = normalize_phone_to_E164($_REQUEST['Caller']);

$response->say($text);

if(!empty($menu_items[$caller])) {
	$response->redirect($menu_items[$caller]);
}
else {
	$response->redirect($fallback);
}

$response->respond();