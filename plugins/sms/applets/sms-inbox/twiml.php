<?php

$response = new Response();

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

$required_params = array('SmsSid', 'From', 'To', 'Body');
$sms_found = true;
foreach($required_params as $param)
{
	if(!in_array($param, array_keys($_REQUEST)))
	{
		$sms_found = false;
	}
}

if($sms_found)
{
	OpenVBX::addSmsMessage($forward,
						   $_REQUEST['SmsSid'],
						   $_REQUEST['From'],
						   $_REQUEST['To'],
						   $_REQUEST['Body']
						   );
}
else
{
	$response->addSms('Unable to send sms message');
}

$response->Respond();