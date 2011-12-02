<?php

class dialGroupTest extends OpenVBX_Applet_TestCase {
	private $group_id = 1;
	
	public function setUp() {
		parent::setUp();		
		
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '',
			'sms_data' => '',
			'tenant_id' => 1
		));				
	
		// set up the request
		$this->setPath('/twiml/applet/voice/1/4a7eed');
		$this->setRequestMethod('POST');
	
		// prepare the header token for request validation
		$this->setRequestToken();	
	}
	
	public function tearDown() {		
		$this->CI->db->truncate('group_messages');
		$this->CI->db->truncate('group_annotations');
		$this->CI->db->truncate('user_messages');
		$this->CI->db->truncate('groups_users');
		$this->CI->db->truncate('messages');
	}
	
	public function testGroupDial() {
		$this->setFlowVar('data', '{"start":{"name":"Call Start","data":{"next":"start/4a7eed"},"id":"start","type":"standard---start"},"4a7eed":{"name":"Dial","data":{"dial-whom-selector":"user-or-group","dial-whom-user-or-group_id":"'.$this->group_id.'","dial-whom-user-or-group_type":"group","dial-whom-number":"","callerId":"","no-answer-action":"voicemail","no-answer-group-voicemail_say":"","no-answer-group-voicemail_play":"","no-answer-group-voicemail_mode":"","no-answer-group-voicemail_tag":"global","no-answer-group-voicemail_caller_id":"+14158774003","number":"","library":"","no-answer-redirect":"","no-answer-redirect-number":"start/4a7eed/e18b35","version":"3"},"id":"4a7eed","type":"standard---dial"},"e18b35":{"name":"Hangup","data":{},"id":"e18b35","type":"standard---hangup"}}');
		
		ob_start();
		$this->CI->voice('1', '4a7eed');
		$out = ob_get_clean();

		// test valid xml
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
	}
}