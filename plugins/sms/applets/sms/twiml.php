<?php
$sms = AppletInstance::getValue('sms');
$next = AppletInstance::getDropZoneUrl('next');

$response = new TwimlResponse;
if(AppletInstance::getFlowType() == 'voice') $response->sms($sms); else $response->message($sms);
if(!empty($next))
{
	$response->redirect($next);
}

$response->respond();
