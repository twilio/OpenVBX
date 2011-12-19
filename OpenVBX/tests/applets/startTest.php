<?php

include_once dirname(__FILE__).'/../CIUnit.php';

class startTest extends OpenVBX_Applet_TestCase
{

	public function setUp() 
	{
		parent::setUp();

		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/f1e974"},"id":"start","type":"standard---start"}}',
			'sms_data' => '{"start":{"name":"Message Received","data":{"next":"start/f1e974"},"id":"start","type":"standard---start"}}',
			'tenant_id' => 1
		));

		// set up our request
		$this->setPath('/twiml/applet/voice/1/f1e974');
		$this->setRequestMethod('POST');
		
		// prepare the header token for request validation
		$this->setRequestToken();
	}
	
	public function testHangupTwimlVoice()
	{
		ob_start();
		$this->CI->start_voice(1);
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);

		$this->assertRegExp('|(<Redirect>)|', $out); 
		$this->assertRegExp('|/voice/1/f1e974|', $out);
	}
	
	public function testHangupTwimlSms()
	{
		ob_start();
		$this->CI->start_sms(1);
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertEquals('SimpleXMLElement', get_class($xml));

		$this->assertRegExp('|(<Redirect>)|', $out);
		$this->assertRegExp('|/sms/1/f1e974|', $out);
	}
	
}