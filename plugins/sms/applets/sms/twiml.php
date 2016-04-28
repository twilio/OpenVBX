<?php

$ci = &get_instance();

$ci->load->helper('format_helper');
$ci->load->helper('token_helper');
$ci->load->library('DialList');

$sms = AppletInstance::getValue('sms');
$next = AppletInstance::getDropZoneUrl('next');
$message_whom_selector = AppletInstance::getValue('message-whom-selector', 'caller');
$message_whom_user_or_group = AppletInstance::getUserGroupPickerValue('message-whom-user-or-group');
$message_whom_number = AppletInstance::getValue('message-whom-number');
$from_number = AppletInstance::getValue('from-number', null);

switch($message_whom_selector) {
	case 'user-or-group':
		// create a dial list from the input state
		$dial_list = DialList::get($message_whom_user_or_group);

		while ($device = $dial_list->next())
		{
			if ($device instanceof VBX_Device && $device->sms)
			{
				if (strpos($device->value, 'client:') !== false)
				{
					$to_number = str_replace('client:', '', $device->value);
				}
				else
				{
					$to_number = $device->value;
				}
				break;
			}
		}
		break;
	case 'number':
		$to_number = normalize_phone_to_E164($message_whom_number);
		break;
	case 'caller':
	default:
		$to_number = $_REQUEST['From'];
}

if($from_number == '') {
	$from_number = $_REQUEST['From'];
}
else if($from_number == 'called') {
	$from_number = $_REQUEST['To'];
}

$sms = token_replace($sms);

$response = new TwimlResponse;

$message_opts = array(
	'to' => $to_number,
	'from' => $from_number,
);

// Call flows still use the legacy <Sms> TwiML
// for sending messages during calls.
if(AppletInstance::getFlowType() == 'voice')
{
	$response->sms($sms, $message_opts);
}
else
{
	$response->message($sms, $message_opts);
}

if(!empty($next))
{
	$response->redirect($next);
}

$response->respond();
