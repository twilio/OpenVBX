<?php
$ci = &get_instance();

$ci->load->helper('format_helper');

function token_replace($input, $reset = false) {
    static $tokens = null;

    if(!isset($tokens) || $reset){
        $tokens = array();
        $token_names = token_names();
        foreach ($token_names as $token) {
            if(isset($_REQUEST[$token])) {
                $tokens['{' . $token . '}'] = $_REQUEST[$token];
                switch ($token) {
                    case 'To':
                    case 'From':
                        $tokens['{' . $token . '_Formatted}'] = format_phone($_REQUEST[$token]);
                        break;
                    default:
                }
            }
        }
    }

    return strtr($input, $tokens);
}

function token_list() {
    $token_list = '';
    foreach(token_names() as $token){
        $token_list .= "<li>{{$token}}</li>";
    }
    return "<ul>$token_list</ul>";
}

function token_names(){
    return array(
        'CallSid',
        'AccountSid',
        'From',
        'To',
        'CallStatus',
        'ApiVersion',
        'Direction',
        'ForwardedFrom',
        'CallerName',
        'FromCity',
        'FromState',
        'FromZip',
        'FromCountry',
        'ToCity',
        'ToState',
        'ToZip',
        'ToCountry',
        'DialCallStatus',
        'DialCallSid',
        'DialCallDuration',
        'RecordingUrl',

        'MessageSid',
        'Body',
        'NumMedia',
        'MediaContentType1',
        'MediaUrl1',
    );
}
