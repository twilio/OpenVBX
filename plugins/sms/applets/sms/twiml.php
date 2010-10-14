<?php
$sms = AppletInstance::getValue('sms');
$next = AppletInstance::getDropZoneUrl('next');

$response = new Response();
$response->addSms($sms);
if(!empty($next))
{
	$response->addRedirect($next);
}

$response->Respond();