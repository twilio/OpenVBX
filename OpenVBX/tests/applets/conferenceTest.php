<?php

class conferenceTest extends OpenVBX_Applet_TestCase {
	public function setUp() {
		parent::setUp();
		// set up the flow data
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/7b0c75"},"id":"start","type":"standard---start"},"7b0c75":{"name":"Conference","data":{"moderator_id":"1","moderator_type":"user","wait-url":"http://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient","conf-id":"conf_4ed7c201a541e"},"id":"7b0c75","type":"standard---conference"}}',
			'sms_data' => '',
			'tenant_id' => 1	
		));
		
		// set up the request
		$this->setPath('/twiml/applet/voice/1/7b0c75');
		$this->setRequestMethod('POST');
		
		// prepare the header token for request validation
		$this->setRequestToken();
	}
		
	public function testConferenceUserModerator() {
		$this->setRequest(array(
			'From' => '+14150001111'
		));
		
		ob_start();
		$this->CI->voice('1', '7b0c75');
		$out = ob_get_clean();

		// test valid xml
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		
		// test presence of conference verb
		$this->assertRegExp('|<Conference(.*?)>(.*?)</Conference>|', $out);
		
		// test moderator
		$this->assertTrue(strpos($out, 'endConferenceOnExit="true"') !== false);
		
		// test hold music
		$this->assertTrue(strpos($out, 'com.twilio.music.ambient') !== false);
	}
	
	public function testConferenceUserParticipant() {
		$this->setRequest(array(
			'From' => '+14150000000'
		));
		
		ob_start();
		$this->CI->voice('1', '7b0c75');
		$out = ob_get_clean();
		
		// test valid xml
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		
		// test non-moderator
		$this->assertTrue(strpos($out, 'endConferenceOnExit="false"') !== false);
	}
}