<?php

class dialNumberTest extends OpenVBX_Applet_TestCase {
	private $dial_number = '4150000000';
	private $caller_id = '4151112222';
	
	public function setUp() {
		parent::setUp();
		
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/4a7eed"},"id":"start","type":"standard---start"},"4a7eed":{"name":"Dial","data":{"dial-whom-user-or-group_id":"","dial-whom-user-or-group_type":"","dial-whom-selector":"number","dial-whom-number":"'.$this->dial_number.'","callerId":"","no-answer-action":"voicemail","no-answer-group-voicemail_say":"","no-answer-group-voicemail_play":"","no-answer-group-voicemail_mode":"","no-answer-group-voicemail_tag":"global","no-answer-group-voicemail_caller_id":"+14158774003","number":"","library":"","no-answer-redirect":"","no-answer-redirect-number":"start/4a7eed/e18b35","version":"3"},"id":"4a7eed","type":"standard---dial"},"e18b35":{"name":"Hangup","data":{},"id":"e18b35","type":"standard---hangup"}}',
			'sms_data' => '',
			'tenant_id' => 1
		));
			
		// set up the request
		$this->setPath('/twiml/applet/voice/1/4a7eed');
		$this->setRequestMethod('POST');
	
		// prepare the header token for request validation
		$this->setRequestToken();
		
		$this->setRequest(array(
			'CallSid' => 'CA12345',
			'AccountSid' => 'AC12345',
			'From' => NULL,
			'To' => NULL,
			'Direction' => 'inbound',
			'CallStatus' => 'ringing'
		));		
	}

	public function testNumberDialNoCallerId() {		
		ob_start();
		$this->CI->voice('1', '4a7eed');
		$out = ob_get_clean();

		// test valid xml
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		
		$this->assertRegExp('|<Number>'.$this->dial_number.'</Number>|', $out);
		$this->assertRegExp('|callerId=""|', $out);
	}
	
	public function testNumberDialCallerId() {
		$this->setRequest(array(
			'From' => $this->caller_id
		));
		
		ob_start();
		$this->CI->voice('1', '4a7eed');
		$out = ob_get_clean();

		// test valid xml
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		
		$this->assertRegExp('|<Number>'.$this->dial_number.'</Number>|', $out);
		$this->assertRegExp('|callerId="'.$this->caller_id.'"|', $out);
	}
	
	public function testDialActionAnswered() {
		$this->markTestIncomplete();
	}
	
	public function testDialActionNoAnswer() {
		$this->markTestIncomplete();		
	}
}