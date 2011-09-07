<?php
$sms = AppletInstance::getValue('sms');
$next = AppletInstance::getDropZoneUrl('next');

$response = new TwimlResponse;
$response->sms($sms);
if(!empty($next))
{
	$response->redirect($next);
}

$response->respond();