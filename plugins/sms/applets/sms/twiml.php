<?php
$sms = AppletInstance::getValue('sms');
$next = AppletInstance::getDropZoneUrl('next');

$response = new TwimlResponse;
$response->message($sms);
if(!empty($next))
{
	$response->redirect($next);
}

$response->respond();