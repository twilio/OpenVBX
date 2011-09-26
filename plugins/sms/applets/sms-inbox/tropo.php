<?php

$tropo = new Tropo;

$forward = AppletInstance::getUserGroupPickerValue('forward');

$devices = array();
switch(get_class($forward))
{
	case 'VBX_User':
		foreach($forward->devices as $device)
		{
			$devices[] = $device;
		}
		$voicemail = $forward->voicemail;
		break;
	case 'VBX_Group':
		foreach($forward->users as $user)
		{
			$user = VBX_User::get($user->user_id);
			foreach($user->devices as $device)
			{
				$devices[] = $device;
			}
		}
		$voicemail = $groupVoicemail;
		break;
	default:
		break;
}

try {
	$tropo_session = new Session_Tropo($_COOKIE['tropo_session']);
} catch (Exception $e) {
	$tropo_session = null;
}

if ($tropo_session) {
	$sms_found = true;
} else {
	$sms_found = false;
}

if($sms_found)
{
	$from = $tropo_session->getFrom();
	$fromNumber = "+".$from['id'];
	$to = $tropo_session->getTo();
	$toNumber = "+".$to['id'];

	OpenVBX::addSmsMessage($forward,
						   $tropo_session->getId(),
						   $fromNumber,
						   $toNumber,
						   $tropo_session->getInitialText(),
						   'tropo'
						   );
}
else
{
	$tropo->say('Unable to send sms message');
}

$tropo->renderJSON();