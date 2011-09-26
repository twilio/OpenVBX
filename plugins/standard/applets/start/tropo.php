<?php 
$tropo = new Tropo();

$next = AppletInstance::getDropZoneUrl('next');
if (!empty($next))
{
	$tropo->on(array('event'=>'continue', 'next'=>$next));
}

// } else if ($message && $to && !$type) {
// 	$tropo->call($to,
// 		array('channel'=>'TEXT', 'from'=>$from));
// 	$tropo->say($message);

$tropo->renderJSON();

// Simple Example Flow JSON:
// {"start":{"name":"Call Start","data":{"next":"start/d73c02"},"id":"start","type":"standard---start"},"d73c02":{"name":"Greeting","data":{"prompt_say":"Hello and welcome to the Open VBX conversion demo IVR!","prompt_play":"","prompt_mode":"say","prompt_tag":"global","number":"","library":"","next":""},"id":"d73c02","type":"standard---greeting"}}
