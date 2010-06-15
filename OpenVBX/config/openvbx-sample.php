<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Make the necessary changes and rename this file to openvbx.php
 *
 */


/* Base URL: This must be the root of your website, WITH a trailing slash.
 * 		http://example.com/
 */

$config['base_url'] = '';

/*
 * OpenVBX needs a connection to Twilio to interact with the API.
 * Specify your account sid and token here. If you don't know it,
 * you can get it here: https://www.twilio.com/user/account/
*/

$config['twilio_sid'] = '';
$config['twilio_token'] = '';


// Dashboard RSS feed: the RSS feed shown on the home page
$config['dash_rss'] = 'feed://feeds.feedburner.com/twilio/OBEN';

// When sending emails OpenVBX will use this address
$config['from_email'] = 'OpenVBX <openvbx@twilio.com>';

// Name of the UI theme to use corresponding to a directory name in /assets/themes
$config['theme'] = 'default';
