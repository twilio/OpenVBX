<?php
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
 */
	
class GoogleUtilityClientException extends Exception {}

class GoogleLoginChallenge
{
	public static $errors = array(
									  'BadAuthentication' => 'The login request used a username or password that is not recognized.',
									  'NotVerified' => 'The account email address has not been verified. The user will need to access their Google account directly to resolve the issue before logging in using a non-Google application.',
									  'TermsNotAgreed' => 'The user has not agreed to terms. The user will need to access their Google account directly to resolve the issue before logging in using a non-Google application. ',
									  'CaptchaRequired' => 'A CAPTCHA is required. (A response with this error code will also contain an image URL and a CAPTCHA token.)',
									  'ServiceUnavailable' => 'The service is not available; try again later.', 
									  'ServiceDisabled' => 'The user\'s access to the specified service has been disabled. (The user account may still be valid.)',
									  'AccountDisabled' => 'The user account has been disabled.',
									  'AccountDeleted' => 'The user account has been deleted.',
									  'Unknown' => 'The error is unknown or unspecified; the request contained invalid input or was malformed.',
									  );

		public static $public_errors = array(
									  'BadAuthentication' => 'Email or password were incorrect',
									  'NotVerified' => 'The account email address has not been verified. The user will need to access their Google account directly to resolve the issue before logging in using a non-Google application.',
									  'TermsNotAgreed' => 'The user has not agreed to terms. The user will need to access their Google account directly to resolve the issue before logging in using a non-Google application. ',
									  'CaptchaRequired' => 'A CAPTCHA is required.',
									  'ServiceUnavailable' => 'The service is not available; try again later.', 
									  'ServiceDisabled' => 'The user\'s access to the specified service has been disabled. (The user account may still be valid.)',
									  'AccountDisabled' => 'The user account has been disabled.',
									  'AccountDeleted' => 'The user account has been deleted.',
									  'Unknown' => 'The error is unknown or unspecified; the request contained invalid input or was malformed.',
									  );

	public static function get_error_message($key)
	{
		return self::$public_errors[$key];
	}

	public static function get_error_code($key)
	{
		$error_keys = array_keys(self::$errors);
		return array_search($key, $error_keys);
	}

	public static function get_error($code) {
		$error_keys = array_keys(self::$errors);
		return $error_keys[$code];
	}
}

class GoogleUtilityClient
{
	public $domain;
	
	private static $curl = null;

	private $authenticated = false;
	private $auth_token;
	private $api_key;
	private $api_secret;
	private $account_type;

	private $login_token;
	private $login_captcha;
	
	private $memcache;
	
	public function __construct($api_key,
								$api_secret,
								$account_type = 'HOSTED_OR_GOOGLE',
								$login_captcha = '',
								$login_token = '',
								$cache_config = NULL)
	{

		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
		$this->login_token = $login_token;
		$this->login_captcha = $login_captcha;
		$this->account_type = $account_type;
		$keys = explode('@', $this->api_key);
		if(count($keys) == 2)
		{
			$this->domain = $keys[1];
		}
		
		if(empty($this->domain))
		{
			throw new GoogleUtilityClientException('Invalid api_key');
		}
		
		if(!empty($cache_config))
		{
			$this->setup_memcache($cache_config['memcache_servers'],
								  $cache_config['memcache_port'],
								  $cache_config['key_prefix']);
		}

	}

	public function setup_memcache($memcache_servers, $memcache_port, $key_prefix)
	{
		$this->memcache = new Memcache();
		foreach ($memcache_servers as $memcache_server)
		{
			$this->memcache->addServer($memcache_server, $memcache_port);
		}
		$this->key_prefix = $key_prefix;
	}


	public function build_key($url, $req_per_hour=1)
	{
		$stamp = intval(time() * ($req_per_hour / 3600));
		return $this->key_prefix . ':' . $stamp . ':' . $url;
	}

	function fetch($url, $method, $args, $req_per_hour=1)
	{
		if(!$this->memcache)
		{
			return $this->perform_request($url, $method, $args);
		}
		
		$key = $this->build_key($url, $req_per_hour);
		$value = $this->memcache->get($key);
		
		if (!$value)
		{
			$value = $this->perform_request($url, $method, $args);
			$value = json_encode($value);
			$this->memcache->set($key, $value);
		}
		
		if (!$value)
		{
			return null;
		}
		return json_decode($value, true);
	}

	public function perform_request($url, $method, $args)
	{
		$method = strtoupper($method);
		switch($method)
		{
			case 'GET':
				break;
			case 'UPDATE':
			case 'DELETE':
			case 'PUT':
				curl_setopt(self::$curl, CURLOPT_CUSTOMREQUEST, $method);
				break;
			case 'POST':
				curl_setopt(self::$curl, CURLOPT_POSTFIELDS, http_build_query($args));
				curl_setopt(self::$curl, CURLOPT_POST, true);
				break;
		}
		
		// Send the HTTP request.
		curl_setopt(self::$curl, CURLOPT_URL, $url);
		curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt(self::$curl, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml',
														   'Authorization: GoogleLogin auth='.$this->auth_token));

		
		$response = curl_exec(self::$curl);
		// Throw an exception on connection failure.
		if (!$response) throw new GoogleAuthenticationClientError('Connection failed');
		
		// Deserialize the response string and store the result.
		$result = self::response_decode($response);
		
		return $result;
	}

	public function authenticate()
	{
		$auth_url = "https://www.google.com/accounts/ClientLogin";
		if (is_null(self::$curl))
		{
			self::$curl = curl_init();
		}
		
		$args = array(
					  'Email' => $this->api_key,
					  'Passwd' => $this->api_secret,
					  'accountType' => $this->account_type,
					  'service' => 'apps',
					  'source' => 'twilio-openVBX-1.0',
					  'logintoken' => $this->login_token,
					  'logincaptcha' => $this->login_captcha,
					  );
		error_log(var_export($args, true));
		curl_setopt(self::$curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt(self::$curl, CURLOPT_POST, true); 
		curl_setopt(self::$curl, CURLOPT_POSTFIELDS, http_build_query($args));
		curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt(self::$curl, CURLOPT_URL, $auth_url);
		$response = curl_exec(self::$curl);
		$response = explode("\n", $response);
		$auth_response = array();
		foreach($response as $response_pair)
		{
			$response_pair = explode('=', $response_pair);
			$auth_response[$response_pair[0]] = isset($response_pair[1])? $response_pair[1] : '';
			$auth_response[$response_pair[0]] .= isset($response_pair[2])? '='.$response_pair[2] : '';
		}

		if(!empty($auth_response['Auth']))
		{
			$this->auth_token = $auth_response['Auth'];
			$this->authenticated = true;
			
			return $this->authenticated;
		}

		if(!empty($auth_response['Error']))
		{
			$this->auth_response = $auth_response;
			
			$error_code = GoogleLoginChallenge::get_error_code($auth_response['Error']);
			$error_message = GoogleLoginChallenge::get_error_message($auth_response['Error']);

			throw new GoogleUtilityClientException($error_message, $error_code);
		}
	}


	public function __call($method, $args)
	{
		
		static $api_cumulative_time = 0;
		$time = microtime(true);
		
		// Initialize CURL 
		self::$curl = curl_init();

		if(isset($args[0]))
		{
			$name = $args[0];
		}

		if(isset($args[1]))
		{
			$args = $args[1];
		}


		curl_setopt(self::$curl, CURLOPT_HTTPHEADER, array('Content-type: application/atom+xml',
														   'Authorization: GoogleLogin auth='.$this->auth_token));

		if(preg_match('/^(http)/', $name, $matches) > 0)
		{
			$url = $name;
		}
		else
		{
			$url = 'https://apps-apis.google.com/a/feeds'
				. '/'
				. $name;
		}
			
		$response = $this->fetch($url, $method, $args);
		
		// If the response is a hash containing a key called 'error', assume
		// that an error occurred on the other end and throw an exception.
		if (isset($response['error']))
		{
			throw new GoogleAuthenticationClientError($response['error'], $response['code']);
		}
		else
		{
			return $response['result'];
		}
	}

	function response_decode($xml)
	{
		$error = NULL;
		$result = NULL;
		$doc = @DOMDocument::loadXML($xml);
		if(!$doc)
		{
			throw new GoogleUtilityClientError('Server Unavailable', 500);
		}
		
		$result["list"] = $doc;
	
		return array("result" => $result);
	}

}

class GoogleUtilityClientNSWrapper
{
	private $object;
	private $ns;
	
	function __construct($obj, $ns)
	{
		$this->object = $obj;
		$this->ns = $ns;
	}
	
	function __call($method, $args)
	{
		$args = array_merge(array($this->ns), $args);
		return call_user_func_array(array($this->object, $method), $args);
	}
}

?>
