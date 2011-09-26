<?php

if (isset($_GET['hangup']) && $_GET['hangup']) {
	$tropo = new Tropo;
	$tropo->hangup();
	$tropo->renderJSON();
	exit;
} else if (isset($_GET['voicemail']) && $_GET['voicemail']) {
	// TODO: Tropo voicemail function
	$tropo = new Tropo;
	$tropo->say("Please leave your message after the tone. Goodbye.");
	$tropo->renderJSON();
	exit;
} else  if (isset($_GET['numbers']) && $_GET['numbers']) {
	// process into numbersToDial
	$numbersToDial = explode(',', $_GET['numbers']);
} else {

	$CI = &get_instance();
	$CI->load->library('DialList');

	$dial_whom_selector = AppletInstance::getValue('dial-whom-selector');
	$dial_whom_user_or_group = AppletInstance::getUserGroupPickerValue('dial-whom-user-or-group');
	$dial_whom_number = AppletInstance::getValue('dial-whom-number');

	$numbersToDial = array();
	if ($dial_whom_selector == 'user-or-group') {
		// Get the numbers to dial
		$dial_list = DialList::get($dial_whom_user_or_group);

		do {
			$to_dial = $dial_list->next();
			if ($to_dial instanceof VBX_User) {
				foreach ($to_dial->devices as $device) {
					if ($device->is_active) {
						if (strpos($device->value, 'client:') !== false) {
							// Dial phono address for this client
							$phono = VBX_Device::get(array(
								'user_id' => $device->user_id,
								'name' => 'Phono',
								'tenant_id' => $device->tenant_id
							));
							array_unshift($numbersToDial, $phono->value);
						} else {
							$numbersToDial[] = $device->value;
						}
					}
				}
			} else if ($to_dial instanceof VBX_Device) {
				$device = $to_dial;
				if ($device->is_active) {
					if (strpos($device->value, 'client:') !== false) {
						// Dial phono address for this client
						$phono = VBX_Device::get(array(
							'user_id' => $device->user_id,
							'name' => 'Phono',
							'tenant_id' => $device->tenant_id
						));
						array_unshift($numbersToDial, $phono->value);
					} else {
						$numbersToDial[] = $device->value;
					}
				}
			}
		} while(!$dialed && ($to_dial instanceof VBX_User || $to_dial instanceof VBX_Device));
	} else {
		$numbersToDial = array($dial_whom_number);
	}

}

$tropo = new Tropo;

$number = array_shift($numbersToDial);
if (strpos($number, '+') === false &&
		strpos($number, 'sip:') === false) {
	$number = "+$number";
}

$tropo->transfer($number, array(
	'timeout'=>20
));

// Processed if one of the callers doesn't pick up
if (strpos($_SERVER['REQUEST_URI'], '?') !== false)
	$uri = substr($_SERVER['REQUEST_URI'], 0,
		strpos($_SERVER['REQUEST_URI'], '?'));
else
	$uri = $_SERVER['REQUEST_URI'];
if (!empty($numbersToDial)) {
	$queryString = str_replace('+', '', (implode(',', $numbersToDial)));
	$tropo->on(array('event'=>'incomplete', 
		'next'=>$uri."?numbers=$queryString"));
	$tropo->on(array('event'=>'error', 
		'next'=>$uri."?numbers=$queryString"));
} else {
	$tropo->on(array('event'=>'incomplete',
		'next'=>$uri.'?voicemail=1'));
	$tropo->on(array('event'=>'error', 
		'next'=>$uri."?voicemail=1"));
}

$tropo->on(array('event'=>'hangup',
	'next'=>$uri.'?hangup=1'));

$tropo->renderJSON();