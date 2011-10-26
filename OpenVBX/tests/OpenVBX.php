<?php

class OpenVBX_TestCase extends CIUnit_TestCase {

	protected $request_method = 'GET';
	protected $schema_file = 'assets/twilioRealtimeCallSchema.xsd';

	/**
	 * Mostly here to hush up errors and set common environment
	 * variables needed to mock a request
	 * 
	 * @return void
	 */
	public function __construct() 
	{
		$this->setServer(array(
			'SERVER_NAME' => 'openvbx.local',
			'HTTP_HOST' => 'openvbx.local'
		));
	}

	/**
	 * - Setting output buffering capture here seems to be the only
	 *   way to control output of the request.
	 *
	 * @return void
	 */
	public function setUp() 
	{
		parent::setUp();
	}
	
	public function tearDown() 
	{
		parent::setUp();
	}

	public function setRequestMethod($method) 
	{
		$this->request_method = $method;
	}

	public function setPath($path) 
	{
		$GLOBALS['vbxsite'] = $path;
		$this->setServer(array(
			'REQUEST_URI' => $path
		));
	}

	public function setServer($array = array()) 
	{
		if (is_array($array)) 
		{
			foreach ($array as $key => $val) 
			{
				$GLOBALS['_SERVER'][$key] = $val;
			}
		}
	}
	
	public function setSession($array = array()) 
	{
		if (is_array($array)) 
		{
			foreach ($array as $key => $val) 
			{
				$this->ci->session->set_userdata($key, $val);
			}
		}
	}
	
	public function setRequest($array = array()) 
	{	
		if (is_array($array)) 
		{
			foreach ($array as $key => $val) 
			{
				$_REQUEST[$key] = $val;
				if ($this->request_method == 'GET') 
				{
					$_GET[$key] = $val;
				}
				else 
				{
					$_POST[$key] = $val;
				}
			}
		}
	}
	
	/**
	 * To properly mimic a HuRL request
	 *
	 * @return void
	 */
	public function setRequestToken() 
	{
		$CI =& get_instance();
		$request_uri = site_url($GLOBALS['_SERVER']['REQUEST_URI']);
		$params = $this->request_method == 'POST' ? $_POST : $_GET;
		
		$_validator = new Services_Twilio_RequestValidator($CI->twilio_token);
		$this->setServer(array(
			'HTTP_X_TWILIO_SIGNATURE' => $_validator->computeSignature($request_uri, $params)
		));
	}
}
