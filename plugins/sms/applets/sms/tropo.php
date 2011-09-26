<?php

$tropo = new Tropo;

$sms = AppletInstance::getValue('sms');
$next = AppletInstance::getDropZoneUrl('next');

$tropo->say($sms);
if(!empty($next))
{
	$tropo->on(array('event'=>'continue',
		'next'=>$next));
}

$tropo->renderJSON();