<?php
include_once('TwimlDial.php');
define('DIAL_COOKIE', 'state-'.AppletInstance::getInstanceId());

$CI =& get_instance();
$CI->load->library('DialList');
$transcribe = (bool) $CI->vbx_settings->get('transcriptions', $CI->tenant->id);
$voice = $CI->vbx_settings->get('voice', $CI->tenant->id);
$language = $CI->vbx_settings->get('voice_language', $CI->tenant->id);
$timeout = $CI->vbx_settings->get('dial_timeout', $CI->tenant->id);

$dialer = new TwimlDial(array(
	'transcribe' => $transcribe,
	'voice' => $voice,
	'language' => $language,
	'sequential' => true,
	'timeout' => $timeout
));
$dialer->set_state();

/**
 * Respond based on state
 * 
 * **NOTE** dialing is done purely on a sequential basis for now.
 * Due to a limitation in Twilio Client we cannot do simulring.
 * If ANY device picks up a call Client stops ringing.
 * 
 * The flow is as follows:
 * - Single User: Sequentially dial devices. If user is online
 *   then the first device will be Client.
 * - Group: Sequentially dial each user's 1st device. If user
 *   is online Client will be the first device.
 * - Number: The number will be dialed.
 */ 
try {
	switch ($dialer->state) {
		case 'voicemail':
			$dialer->noanswer();
			break;
		case 'hangup':
			$dialer->hangup();
			break;
		case 'recording':
			$dialer->add_voice_message();
			break;
		default:
			if ($dialer->dial_whom_selector === 'user-or-group') 
			{
				// create a dial list from the input state
				$dial_list = DialList::get($dialer->dial_whom_user_or_group);

                $dialed = false;

				while (count($dial_list)) 
				{
					$to_dial = $dial_list->next();
					if ($to_dial instanceof VBX_Device) 
					{
						$dialed = $dialer->dial($to_dial);
					}
				}
	
				if (!$dialed) 
				{
					// nobody to call, push directly to voicemail
					$dialer->noanswer();
				}
			}
			elseif ($dialer->dial_whom_selector === 'number')
			{
				$dialer->dial($dialer->dial_whom_number);
			}
			break;
	}
}
catch (Exception $e) {
	error_log('Dial Applet exception: '.$e->getMessage());
	$dialer->response->say("We're sorry, an error occurred while dialing. Goodbye.");
	$dialer->hangup();
}

$dialer->save_state();
$dialer->respond();
