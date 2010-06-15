<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$config['openid_storepath'] = BASEPATH.'../tmp';
$config['openid_policy'] = 'openidauth/policy';
$config['openid_required'] = array('nickname');
$config['openid_optional'] = array('fullname', 'email');
$config['openid_request_to'] = 'openidauth/check';
$config['consumer_key'] = '';
$config['consumer_secret'] = '';
?>
