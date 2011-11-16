<?php

/**
 * Overrides for "pluggable" functions located in
 * `OpenVBX/helpers/twilio_helper.php`
 */

/**
 * Override the REST request validation to always let ourselves in
 *
 * @param string $failure_message 
 * @return bool true
 */
function validate_rest_request($failure_message = 'Could not validate this request. Goodbye.') {
	return true;
}