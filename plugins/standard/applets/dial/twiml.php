<?php
include_once('TwimlDial.php');
define('DIAL_COOKIE', 'state-'.AppletInstance::getInstanceId());

$CI =& get_instance();
$CI->load->library('DialList');
$dialer = new TwimlDial();

$dialer->set_state();

// Respond based on state
switch ($dialer->state) {
	case 'hangup':
		$dialer->hangup();
		break;
	case 'new':
		if ($dialer->dial_whom_selector === 'user-or-group') {
			// create a dial list from the input state
			$dial_list = DialList::get($dialer->dial_whom_user_or_group);

			$dialed = false;
			do {
				$to_dial = $dial_list->next();
				if ($to_dial instanceof VBX_User || $to_dial instanceof VBX_Device) {
					$dialed = $dialer->dial($to_dial);
					if ($dialed) {
						$dialer->state = $dial_list->get_state();
					}
				}
			} while(!$dialed && ($to_dial instanceof VBX_User || $to_dial instanceof VBX_Device));

			if (!$dialed) {
				// nobody to call, push directly to voicemail
				$dialer->noanswer();
			}
		}
		else {
			// we'll create a token DialList so that we can emulate an 
			// empty state (ie: we're done) and keep the same logic flow
			$dial_list = DialList::load(array());
			$dialer->state = $dial_list->get_state();
			// arbitrary number, simpler handling
			$dialer->dial($dialer->dial_whom_number);
		}
		break;
	case 'recording':
		if(isset($_REQUEST['testing'])) {
			// ?? what is this?
			break;
		}
		$dialer->add_voice_message();
		break;
	default:
		// rolling through users, populate dial list from state
		#$class = $dialer->state['type']; // state tells us wether its a DialList or DialListUser object
		#$dial_list = $class::load($dialer->state);
		// more verbose to be compatible with older versions of PHP
        if ($dialer->state['type'] == 'DialList') {
                $dial_list = DialList::load($dialer->state);
        }
        else {
                $dial_list = DialListUser::load($dialer->state);
        }

		// get the next valid user
		$dialed = false;
		do {
			$to_dial = $dial_list->next();
			if ($to_dial instanceof VBX_User || $to_dial instanceof VBX_Device) {
				$dialed = $dialer->dial($to_dial);
				if ($dialed) {
					$dialer->state = $dial_list->get_state();
				}
			}
		} while(!$dialed && ($to_dial instanceof VBX_User || $to_dial instanceof VBX_Device));
		
		if (!$dialed) {
			// no users left see what next action is, or go to voicemail
			$dialer->noanswer();
		}
		break;
}

$dialer->save_state();
$dialer->Respond();
