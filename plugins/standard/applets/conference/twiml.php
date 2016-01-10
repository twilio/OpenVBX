<?php
$ci = &get_instance();

$moderator = AppletInstance::getUserGroupPickerValue('moderator');
$confId = AppletInstance::getValue('conf-id');
$confName = AppletInstance::getInstanceId() . $confId;
$caller = normalize_phone_to_E164(isset($_REQUEST['From'])? $ci->input->get_post('From') : '');
$isModerator = false;
$defaultWaitUrl = 'http://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient';
$waitUrl = AppletInstance::getValue('wait-url', $defaultWaitUrl);
$record = AppletInstance::getValue('record','do-not-record');

$hasModerator = false;

if (!is_null($moderator)) {
	$hasModerator = true;
	switch(get_class($moderator))
	{
		case 'VBX_User':
			foreach($moderator->devices as $device)
			{
				if($device->value == $caller)
				{
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
						$isModerator = true;
					}
				}
			}
			break;
	}
}

$confOptions = array(
	'muted' => (!$hasModerator || $isModerator)? 'false' : 'true',
	'startConferenceOnEnter' => (!$hasModerator || $isModerator)? 'true' : 'false',
	'endConferenceOnExit' => ($hasModerator && $isModerator)? 'true' : 'false',
	'waitUrl' => $waitUrl,
	'record' => $record,
);

$response = new TwimlResponse();

$dial = $response->dial(null, array(
	'timeout' => $ci->vbx_settings->get('dial_timeout', $ci->tenant->id),
	'timeLimit' => 14400
));
$dial->conference($confName, $confOptions);

$response->respond();
