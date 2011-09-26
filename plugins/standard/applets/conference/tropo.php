<?php

// TODO: Bridge twilio and tropo conferences by connecting to the Twilio (or Tropo) phone number.

$tropo = new Tropo;

try {
	$tropo_session = new Session_Tropo($_COOKIE['tropo_session']);
} catch (Exception $e) {
	$tropo_session = null;
}

$moderator = AppletInstance::getUserGroupPickerValue('moderator');
$confId = AppletInstance::getValue('conf-id');
$confName = AppletInstance::getInstanceId() . $confId;
if (!$tropo_session)
	$from = null;
else
	$from = $tropo_session->getFrom();
$caller = normalize_phone_to_E164( isset($from)? $from['name'] : '' );
$isModerator = false;
$defaultWaitUrl = 'http://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient';
$waitUrl = trim(preg_replace('/(<[^>]*>)/', ' ', file_get_contents(AppletInstance::getValue('wait-url', $defaultWaitUrl))));
$waitUrl = substr($waitUrl, 0, strpos($waitUrl, ' '));
// $waitUrl = 'http://com.twilio.music.classical.s3.amazonaws.com/ith_brahms-116-4.mp3';

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

// Get the tropo sessions
$tropoSessions = FlowStore::get('tropo_sessions');

if ($tropoSessions) {
	$tropoSessions = json_decode($tropoSessions);
} else {
	$tropoSessions = array();
}

if (!$tropo_session)
	$tropoSessionId = null;
else
	$tropoSessionId = $tropo_session->getId();

// URL for tropo events
$urlPart = substr($_SERVER['REQUEST_URI'], 
	strrpos($_SERVER['REQUEST_URI'], '/')+1);
if (strpos($_SERVER['REQUEST_URI'], '?') !== false)
	$urlPart = substr($_SERVER['REQUEST_URI'], 0,
		strrpos($_SERVER['REQUEST_URI'], '?'));

// Default events
$tropo->on(array(
	'event' => 'initiate',
	'next' => $urlPart.'?a=initiate'
));
$tropo->on(array(
	'event' => 'callerhangup',
	'next' => $urlPart.'?a=callerhangup'
));
$tropo->on(array(
	'event' => 'hangup',
	'next' => $urlPart.'?a=hangup'
));
$tropo->on(array(
	'event' => 'incomplete',
	'next' => $urlPart.'?a=hangup'
));
$tropo->on(array(
	'event' => 'error',
	'next' => $urlPart.'?a=hangup'
));
$tropo->on(array(
	'event' => 'exit',
	'next' => $urlPart.'?a=exit'
));

if (isset($_GET['a']) && $_GET['a'] == 'initiate') {

	$tropo->say(site_url('/audio/enter.wav'));
	$tropo->conference($confName, array(
		'id'=>$confName
	));
	$tropo->renderJSON(); 

} else if (isset($_GET['a']) && $_GET['a'] == 'hangup') {

	// Hangup this call and send the signal
	$tropoSessionMatch = null;
	foreach ($tropoSessions as $i => $tropoSession) {
		if ($tropoSession->id == $tropoSessionId) {
			$tropoSessionMatch = $i;
		} else {
			$signalValue = $isModerator ? 'exit' : 'callerhangup';
			$ch = curl_init("https://api.tropo.com/1.0/sessions/{$tropoSession->id}/signals?action=signal&value=$signalValue");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_exec($ch);
			curl_close($ch);
		}
	}

	if ($isModerator) {
		FlowStore::delete('tropo_sessions');
	} else if ($tropoSessionMatch !== null) {
		unset($tropoSessions[$tropoSessionMatch]);
		FlowStore::set('tropo_sessions', $tropoSessions);
	}

} else if (isset($_GET['a']) && $_GET['a'] == 'callerhangup') {
	
	// Caller has hung up. 
	$tropo->say(site_url('/audio/exit.wav'));
	$tropo->conference($confName, array(
		'id'=>$confName
	));
	$tropo->renderJSON();

} else if (isset($_GET['a']) && $_GET['a'] == 'exit') {

	// Exit
	$tropo->hangup();
	$tropo->renderJSON();

} else {

	if ($tropoSessionId) {
		$sessionMatch = false;
		foreach ($tropoSessions as $i => $tropoSession) {
			if ($tropoSession->id == $tropoSessionId) {
				$sessionMatch = true;
				break;
			}
		}
		if (!$sessionMatch) {
			$tropoSessions[] = array('id'=>$tropoSessionId);

			FlowStore::set('tropo_sessions', json_encode($tropoSessions));
		}
	}

	// Put the caller on hold first.
	if (count($tropoSessions) == 1 && (!$hasModerator && !$isModerator)) {
		$tropo->say($waitUrl);
		$tropo->on(array(
			'event' => 'continue',
			'next' => $urlPart
		));
	} else {
		// Interrupt the caller IDs
		foreach ($tropoSessions as $i => $tropoSession) {
			// Send interrupt signal
			if ($tropoSession->id <> $tropoSessionId) {
				$ch = curl_init("https://api.tropo.com/1.0/sessions/{$tropoSession->id}/signals?action=signal&value=initiate");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_exec($ch);
				curl_close($ch);
			}
		}
		$tropo->conference($confName, array(
			'id'=>$confName
		));
	}

	$tropo->renderJSON();

}