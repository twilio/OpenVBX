<?php
$moderator = AppletInstance::getUserGroupPickerValue('moderator');
$confId = AppletInstance::getValue('conf-id');
$confName = AppletInstance::getInstanceId() . $confId;
$caller = normalize_phone_to_E164( isset($_REQUEST['From'])? $_REQUEST['From'] : '' );
$isModerator = false;
$defaultWaitUrl = 'http://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient';
$waitUrl = AppletInstance::getValue('wait-url', $defaultWaitUrl);

$hasModerator = false;

if (!is_null($moderator)) {
	switch(get_class($moderator))
	{
		case 'VBX_User':
			foreach($moderator->devices as $device)
			{
				if($device->value == $caller)
				{
					$hasModerator = true;
					$isModerator = true;
				}
			}
			break;
		case 'VBX_Group':
			foreach($moderator->users as $user)
			{
				$user = VBX_User::get($user->user_id);
				foreach($user->devices as $device)
				{
					if($device->value == $caller)
					{
						$hasModerator = true;
						$isModerator = true;
					}
				}
			}
			break;
	}
}

$confOptions = array('muted' => (!$hasModerator || $isModerator)? 'false' : 'true',
					 'startConferenceOnEnter' => (!$hasModerator || $isModerator)? 'true' : 'false',
					 'endConferenceOnExit' => ($hasModerator && $isModerator)? 'true' : 'false',
					 'waitUrl' => $waitUrl,
					 );

$response = new Response();

$dial = $response->addDial();
$dial->addConference($confName, $confOptions);

$response->Respond();
