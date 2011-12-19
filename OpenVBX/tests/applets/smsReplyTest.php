<?php

class smsReplyTest extends OpenVBX_Applet_TestCase {
	
	private $message = 'Thees is a text Messege, Bork bork bork!';
	
	public function setUp() {
		parent::setUp();
		
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => NULL,
			'sms_data' => '{"start":{"name":"Message Received","data":{"next":"start/1f5a31"},"id":"start","type":"standard---start"},"1f5a31":{"name":"Sms","data":{"sms":"'.$this->message.'","next":""},"id":"1f5a31","type":"sms---sms"}}',
			'tenant_id' => 1
		));
		
		// set up our request
		$this->setPath('/twiml/applet/sms/1/1f5a31');
		$this->setRequestMethod('POST');
		
		// prepare the header token for request validation
		$this->setRequestToken();
	}
	
	public function testSmsReply() {
		ob_start();
		$this->CI->sms(1, '1f5a31');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);

		$this->assertRegExp('|(<Sms>'.$this->message.'</Sms>)|', $out);
		$this->assertEquals(0, preg_match('|(<Redirect>)|', $out));
	}
	
	public function testSmsReplyRedirect() {
		$flow_with_redirect = '{"start":{"name":"Message Received","data":{"next":"start/1f5a31"},"id":"start","type":"standard---start"},"1f5a31":{"name":"Sms","data":{"sms":"'.$this->message.'","next":"12345"},"id":"1f5a31","type":"sms---sms"},"12345":{"name":"Dummy", "data":{}, "id":"12345", "type":"sms---sms"}}';
		$this->setFlowVar('sms_data', $flow_with_redirect);

		ob_start();
		$this->CI->sms(1, '1f5a31');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertEquals('SimpleXMLElement', get_class($xml));

		$this->assertEquals(1, preg_match('|(<Redirect>(.*?)/sms/1/12345</Redirect>)|', $out));
	}
}