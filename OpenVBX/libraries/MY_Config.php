<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

class MY_Config extends CI_Config
{

	CONST REGEX_REPLACE_DOUBLE_SLASH = '!([^:\s]{1})(//+)!';

	public static function url_trim($url) {
	    return preg_replace('!([^:\s]{1})(//+)!', '$1/', $url);
	}

	public function site_url($url)
	{
		$ci = &get_instance();
		if(!empty($ci->router->tenant))
		{
			$url = $ci->router->tenant . '/' . $url;
		}

		return self::url_trim(parent::site_url($url));
	}

	public function real_site_url($uri)
	{
		return self::url_trim(parent::site_url($uri));
	}
}

function real_site_url($uri)
{
	$CI =& get_instance();
	return $CI->config->real_site_url($uri);
}

function asset_url($uri)
{
	$CI = &get_instance();
	$url = $CI->config->real_site_url($uri);
	$index_page = $CI->config->item('index_page');
	if(strlen($index_page))
	{
		$test = str_replace($index_page, '', $url);
		return $test;
	}

	return $url;
}

function iphone_handler_url($uri)
{
        return "openvbx://{$_SERVER['SERVER_NAME']}/{$uri}";
}

function tenant_url($uri, $tenant_id = NULL)
{
	$CI = & get_instance();
	if(!$tenant_id)
		$tenant_id = $CI->tenant->id;
	$tenant = $CI->settings->get_tenant_by_id($tenant_id);
	return $CI->config->real_site_url($tenant->url_prefix . '/' . $uri);
}
	
function current_url()
{
	$CI =& get_instance();
	return $CI->config->site_url($CI->uri->uri_string());
}

function redirect($uri = '', $method = 'location', $http_response_code = 302)
{
	if(!headers_sent())
	{
		$ci = &get_instance();
		if(is_object($ci)
		   && isset($ci->session)
		   && is_object($ci->session))
		   $ci->session->persist();
	}
	else
	{
		error_log('Unable to write session, headers already sent');
	}

	if ( ! preg_match('#^https?://#i', $uri))
	{
		$uri = site_url($uri);
	}

	switch($method)
	{
		case 'refresh'	: header("Refresh:0;url=".$uri);
			break;
		default			: header("Location: ".$uri, TRUE, $http_response_code);
			break;
	}
	exit;
}

?>
