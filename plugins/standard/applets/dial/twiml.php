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
		if ($dialer->dial_whom_user_or_group instanceof VBX_User || $dialer->dial_whom_user_or_group instanceof VBX_Group) {
			// create a dial list from the input state
			$dial_list = DialList::get($dialer->dial_whom_user_or_group);
			$to_dial = $dial_list->next();
			if ($to_dial instanceof VBX_User) {
				$dialer->dial($to_dial);
				$dialer->state = $dial_list->get_state();
			}
			else {
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
			$dialer->addNumber($dialer->dial_whom_or_group);
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
		$dial_list = DialList::load($dialer->state);
		// get the next user
		$to_dial = $dial_list->next();
		if ($to_dial instanceof VBX_User) {
			// we have a user target, dial
			$dialer->dial($to_dial);
			$dialer->state = $dial_list->get_state();
		}
		else {
			// no users left see what next action is, or go to voicemail
			$dialer->noanswer();
		}
		break;
}

$dialer->save_state();
$dialer->Respond();
