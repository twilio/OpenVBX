<?php 
$response = new Response();

$next = AppletInstance::getDropZoneUrl('next');
if (!empty($next))
{
	$response->addRedirect($next);    
}

$response->Respond();

