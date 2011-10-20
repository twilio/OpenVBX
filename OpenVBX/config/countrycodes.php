<?php
/**
 * Formatting information for different country phone number formatting.
 * Used when purchasing phone numbers. Number display should be returned 
 * correctly by Twilio when requesting IncomingNumbers from the API.
 */
$config['countrycodes'] = array(
	'CA' => array('1', '+1 (*)'),
	'GB' => array('44', '+44 (*)'),
	'US' => array('1', '+1 (*)')
);