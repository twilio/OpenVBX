<?php

/**
 * Naive Github API V3 client
 * Only implementing the parts we need
 */
class VBX_Github_Client {
	
	protected $repoOrg = 'twilio';
	protected $repoName = 'OpenVBX';
	
	protected $apiUrl = 'https://api.github.com';
	
	protected $jsonErrors = array(
		JSON_ERROR_NONE	=> 'No error has occurred',
		JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
		JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
		JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
		JSON_ERROR_SYNTAX => 'Syntax error',
		JSON_ERROR_UTF8	=> 'Malformed UTF-8 characters, possibly incorrectly encoded',
	);
	
	/**
	 * get Tags for this Repository
	 *
	 * @return array
	 */
	public function getTags() {
		$response = $this->get('/repos/' . $this->repoOrg .'/' . $this->repoName . '/tags');
		
		$tags = array();
		foreach ($response as $tag) {
			$tags[$tag['name']] = $tag;
		}
				
		return $tags;
	}

	/**
	 * Perform at GET request against the API
	 *
	 * @throws Exception on HTTP or Json decode error
	 * @param string $url 
	 * @return array
	 */
	public function get($url) {
		$getUrl = $this->apiUrl . '/' . ltrim($url, '/');

		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $getUrl);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, OpenVBX::getVbxUserAgentString());
		
		$response = curl_exec($curl);
		
		if (!$response) {
			$err = curl_error($curl);
			$errNo = curl_errno($curl);
			throw new Exception('HTTP communication error: ' . $errNo . ', ' . $err);
		}
		
		curl_close($curl);
		
		$decoded = json_decode($response, true);
		
		if ($jsonErr = $this->isJsonError()) {
			throw new Exception('JSON Error: ' . $jsonErr);
		}
				
		return $decoded;
	} 
	
	/**
	 * Check to see if the last JSON operation resulted in an error
	 *
	 * @return mixed bool|string
	 */
	protected function isJsonError() {
        if (!function_exists('json_last_error')) {
			return false;
		}
		
		$jsonErr = json_last_error();
		
		if ($jsonErr == JSON_ERROR_NONE) {
			return false;
		}
		
		// oh, you lucky php 5.5 users
		if (function_exists('json_last_error_msg')) {
			$jsonErr = json_last_error_msg();
			return is_null($jsonErr) ? false : $jsonErr;
		}

		return isset($this->jsonErrors[$jsonErr]) ? $this->jsonErrors[$jsonErr] : false;
	}
}
