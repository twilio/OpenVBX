<?php 
$response = new TwimlResponse;

$next = AppletInstance::getDropZoneUrl('next');
if (!empty($next))
{
	$response->redirect($next);    
}

$response->respond();

