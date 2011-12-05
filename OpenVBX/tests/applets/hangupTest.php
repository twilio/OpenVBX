<?php

include_once dirname(__FILE__).'/../CIUnit.php';

class hangupTest extends OpenVBX_Applet_TestCase
{
	
	public function setUp()
	{
		parent::setUp();

		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/f1e974"},"id":"start","type":"standard---start"},"f1e974":{"name":"Hangup","data":{},"id":"f1e974","type":"standard---hangup"}}',
			'sms_data' => NULL,
			'tenant_id' => 1
		));

		// set up our request
		$this->setPath('/twiml/applet/voice/1/f1e974');
		$this->setRequestMethod('POST');
		
		// prepare the header token for request validation
		$this->setRequestToken();
	}

	public function testHangupTwimlVerb()
	{
		ob_start();
		$this->CI->voice(1, 'f1e974');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		
		$this->assertRegExp('|(<Hangup/>)|', $out);
	}
}