<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
| 	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['scaffolding_trigger'] = 'scaffolding';
|
| This route lets you set a "secret" word that will trigger the
| scaffolding feature for added security. Note: Scaffolding must be
| enabled in the controller in which you intend to use it.   The reserved
| routes must come before any wildcard or regular expression routes.
|
*/

#$route['default_controller'] = "messages/message_index";
$route['default_controller'] = "iframe";
$route['scaffolding_trigger'] = "";

$route['install'] = "install";
$route['upgrade'] = "upgrade";
$route['upgrade/setup'] = "upgrade/setup";
$route['upgrade/validate'] = "upgrade/validate";
$route['messages'] = "messages/message_index";
$route['messages/inbox/(:num)'] = "messages/inbox/index/$1";
$route['messages/scripts/(:num)'] = "messages/inbox/scripts/$1";
$route['messages/scripts'] = "messages/inbox/scripts";
$route['messages/details/(:num)/(:any)'] = "messages/details/index/$1/$2";
$route['messages/details/(:any)'] = "messages/details/index/$1";
$route['messages/call/(:num)'] = 'messages/message_call/index/$1';
$route['messages/call'] = 'messages/message_call/index';
$route['messages/client'] = 'messages/message_call/client';
$route['messages/sms/(:num)'] = 'messages/message_text/index/$1';
$route['messages/sms'] = 'messages/message_text/index';
$route['accounts'] = "accounts";
$route['account/number/(:any)'] = "devices/number/$1"; // Deprecating account/number
$route['account/number'] = "devices/number"; // Deprecating account/number
$route['account'] = "account";
$route['flows'] = "flows";
$route['numbers/token'] = "numbers/token";
$route['numbers'] = "numbers";
$route['audio'] = "audio";
$route['audiofiles'] = "audiofiles";
$route['log'] = "log";
$route['settings'] = 'settings/settings_index';
$route['settings/site/(:any)/(:num)'] = 'settings/site/index/$1/$2';
$route['settings/site/(:any)'] = 'settings/site/index/$1';
$route['settings/site'] = 'settings/site/index';
$route['auth/reset/(:any)'] = "auth/reset/set_password/$1";
$route['auth'] = "auth";
$route['twiml/start/voice/(:any)'] = "twiml/start_voice/$1";
$route['twiml/start/sms/(:any)'] = "twiml/start_sms/$1";
$route['twiml/applet/voice/(:any)/(:any)'] = "twiml/voice/$1/$2";
$route['twiml/applet/sms/(:any)/(:any)'] = "twiml/sms/$1/$2";
$route['twiml'] = "twiml";
/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
$route['tropo/start/voice/(:any)'] = "tropojson/start_voice/$1";
$route['tropo/start/sms/(:any)'] = "tropojson/start_sms/$1";
$route['tropo/applet/voice/(:any)/(:any)'] = "tropojson/voice/$1/$2";
$route['tropo/applet/sms/(:any)/(:any)'] = "tropojson/sms/$1/$2";
$route['tropo'] = "tropojson";
$route['tropojson'] = "tropojson";
// VoiceVault endpoings. These are initiated from tropo/twilio.
$route['voicevault'] = "voicevault";
$route['voicevault/tropojson'] = "voicevault/tropojson";
$route['voicevault/tropojson/enroll'] = "voicevault/tropojson_enroll";
$route['voicevault/tropojson/reset'] = "voicevault/tropojson_reset";
$route['voicevault/tropojson/enroll/(:any)'] = "voicevault/tropojson_enroll/$1";
$route['voicevault/tropojson/reset/(:any)'] = "voicevault/tropojson_reset/$1";
$route['voicevault/twiml'] = "voicevault/twiml";
$route['voicevault/twiml/enroll'] = "voicevault/twiml_enroll";
$route['voicevault/twiml/reset'] = "voicevault/twiml_reset";
$route['voicevault/twiml/enroll/(:any)'] = "voicevault/twiml_enroll/$1";
$route['voicevault/twiml/reset/(:any)'] = "voicevault/twiml_reset/$1";
/** End Disruptive Technologies code **/
$route['p/(:any)'] = "page/index/$1";
$route['p/(:any)/(:any)'] = "page/index/$1/$2";
$route['p'] = "page/index/$1";
$route['config/(:any)'] = "config/index/$1";
$route['config'] = "config/index/$1";
$route['connect'] = "connect";
$route['dialog'] = "dialog";
$route['client'] = "client";
$route['devices'] = "devices";
$route['voicemail'] = "voicemail";
$route['hook/(:any)'] = "hook/index/$1";
$route['hook/(:any)/(:any)'] = "hook/index/$1/$2";
$route['external/messages/details/(:any)'] = 'external/message_details/$1';
$route['external'] = "external";
$route['iphone/messages/details/(:any)'] = 'iphone/message_details/$1';
$route['iphone'] = "iphone";
$route['iframe'] = "iframe";
$route['test'] = 'test';

/* End of file routes.php */
/* Location: ./system/application/config/routes.php */
