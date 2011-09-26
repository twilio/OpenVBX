<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 **/

// formatting

// make a series of digits into a properly formatted US phone number
function format_phone($number)
{
	$no = preg_replace('/[^0-9+]/', '', $number);

	if(strlen($no) == 11 && substr($no, 0, 1) == "1")
		$no = substr($no, 1);
	elseif(strlen($no) == 12 && substr($no, 0, 2) == "+1")
		$no = substr($no, 2);
	
	if(strlen($no) == 10)
		return "(".substr($no, 0, 3).") ".substr($no, 3, 3)."-".substr($no, 6);
	elseif(strlen($no) == 7)
		return substr($no, 0, 3)."-".substr($no, 3);
	else
		return $no;
	
}

function normalize_phone_to_E164($phone) {
	/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
	// sip: and app: addresses are valid (for tropo)
	if (strpos($phone, 'sip:') !== false ||
			strpos($phone, 'app:') !== false)
		return $phone;
	/** End Disruptive Technologies code **/

	// get rid of any non (digit, + character)
	$phone = preg_replace('/[^0-9+]/', '', $phone);

	// validate intl 10
	if(preg_match('/^\+([2-9][0-9]{9})$/', $phone, $matches)){
		return "+{$matches[1]}";
	}

	// validate US DID
	if(preg_match('/^\+?1?([2-9][0-9]{9})$/', $phone, $matches)){
		return "+1{$matches[1]}";
	}


	// validate INTL DID
	if(preg_match('/^\+?([2-9][0-9]{8,14})$/', $phone, $matches)){
		return "+{$matches[1]}";
	}

	// premium US DID
	if(preg_match('/^\+?1?([2-9]11)$/', $phone, $matches)){
		return "+1{$matches[1]}";
	}

	return $phone;
}  

// return an abbreviated url string. ex: "http://example.com/123/page.htm" => "example.com...page.htm"
function short_url($string, $max_len = 30)
{
	$value = str_replace(array('http://', 'https://', 'ftp://'), '', $string);
	if(strlen($value) > $max_len) {
		$domain = reset(explode('/', $value));
		$domain_len = strlen($domain);
		if($domain_len + 3 >= $max_len) {
			return $domain;
		} else {
			$remaining = strlen($value) - $max_len - $domain_len + 3;
			return $domain . ($remaining > 0 ? '...' . substr($value, -$remaining) : '/');
		}
	} else {
		return $value;
	}
}

function random_str($length = 10) {
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";
	
	$str = '';
	for($a = 0; $a < $length; $a++)
	{
		$str .= $chars[rand(0, strlen($chars) - 1)];
	}

	return $str;
}

function format_player_time($time_in_seconds) {
	$time_in_seconds = intval($time_in_seconds);
	$minutes = floor($time_in_seconds / 60);
	$seconds = $time_in_seconds - ($minutes * 60);

	return sprintf('%02s:%02s', $minutes, $seconds);
}

function format_time_difference($seconds='', $time='') {
	if(!is_numeric($seconds) || empty($seconds)) return true;
	
	$CI =& get_instance();
	$CI->lang->load('date');
	if(!is_numeric($time)) $time = date('U');
	$difference = abs($time-$seconds);
	$periods = array('date_second', 'date_minute', 'date_hour', 'date_day', 'date_week', 'date_month', 'date_year');
	$lengths = array('60','60','24');
	for($j=0; $difference >= $lengths[$j]; $j++) {
		if($j==count($lengths)-1) break;
		$difference /= $lengths[$j];
	}
	
	$difference = round($difference);
	if($difference == 0 && $j==0) $difference = 1;
	if($difference != 1) $periods[$j].= 's';

	if($j == 2 && $difference > 23)
		return date('M j g:i A', $seconds);
	return $difference.' '.strtolower($CI->lang->line($periods[$j])).' ago';
}

function sort_by_date($a, $b)
{
	$a_time = strtotime($a->created);
	$b_time = strtotime($b->created);
	if($a_time == $b_time)
	{
		return 0;
	}
	
	return ($a_time > $b_time)? -1 : 1;
}

function format_short_timestamp($time)
{
	$start_of_today = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
	$start_of_this_year = mktime(0, 0, 0, 1, 1, date("Y"));
	
	// error_log("time: $time >>>> " . date("%r", $time));
	// error_log("start_of_today: $start_of_today >>>> " . date("%r", $start_of_today) );
	// error_log("start_of_this_year: $start_of_this_year >>>> " . date("%r", $start_of_this_year));
	
	if ($time > $start_of_today)
	{
		// return H:MM
		return date("g:i a", $time);
	}
	else if ($time > $start_of_this_year)
	{
		// return something like "Mar 3"
		return date("M j", $time);
	}
	else
	{
		// return M/D/YY
		return date("n/j/y", $time);
	}
}

function format_name($user)
{
	if(is_object($user))
	{
		if(!empty($user->first_name)
		   && !empty($user->last_name))
		{
			return "{$user->first_name} {$user->last_name}";
		}
		return $user->email;
	}

	if(is_array($user))
	{
		if(!empty($user['first_name'])
		   && !empty($user['last_name']))
		{
			return "{$user['first_name']} {$user['last_name']}";
		}

		return $user['email'];
	}

	return '';
}

function format_name_as_initials($user)
{
	if(is_object($user))
	{
		$initials = "";
		
		if (!empty($user->first_name))
		{
			$initials .= substr($user->first_name, 0, 1);
		}
		
		if (!empty($user->last_name))
		{
			$initials .= substr($user->last_name, 0, 1);
		}
		
		return strtoupper($initials);
	}

	return '';
}

function format_url($url)
{
	$str = $url;
	if(preg_match('/^https?:\/\/([^\/]+)\/.*\/([^\/]+)$/i', $url, $matches) > 0)
	{
		$str = $matches[1]
			.'/.../'
			. $matches[2];
	}

	return $str;
}

function html($data)
{
	if(is_string($data))
	{
		return htmlspecialchars($data);
	}

	if(is_array($data))
	{
		foreach($data as $key => $val)
		{
			if(is_string($val))
			{
				$data[$key] = htmlspecialchars($val);
			}
			else if(is_array($val))
			{
				$data[$key] = html($val);
			}
			else if(is_object($val))
			{
				$object_vars = get_object_vars($val);
				foreach($object_vars as $prop => $propval)
				{
					$data[$key]->{$prop} = html($propval);
				}
			}
		}
	}
	return $data;
}