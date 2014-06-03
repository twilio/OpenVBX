<?php

/**
 * Humanize strings
 *
 * @param string $status 
 * @param string $sep 
 * @return string
 */
function humanize($status, $sep = '-') 
{
	return ucwords(str_replace($sep, ' ', $status));
}

/**
 * Format a dialed object based on its type
 * $number will start with 'client:' if it was
 * a call made/answered with the browser phone
 *
 * @param string $number 
 * @return string
 */
function number_text($number) 
{
	if (preg_match('|^client:|', $number))
	{
		$user_id = str_replace('client:', '', $number);
		$user = VBX_User::get(array('id' => $user_id));
		$ret = $user->first_name.' '.$user->last_name.' (client)';
	}
	else
	{
		$ret = format_phone($number);
	}
	return $ret;
}

/**
 * Output a human readable date
 *
 * @param string $date 
 * @return string
 */
function format_date($date)
{
	$timestamp = strtotime($date);
	$date_string = date('M j, Y', $timestamp).'<br />'
					.date('H:i:s T', $timestamp);
	return $date_string;
}