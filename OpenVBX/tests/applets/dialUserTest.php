<?php

class dialUserTest extends OpenVBX_Applet_TestCase {
	private $dial_user_id = 1;
	
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
	
	public function testUserDialNumberNoClient() {
		$this->setFlowVar('data', '{"start":{"name":"Call Start","data":{"next":"start/4a7eed"},"id":"start","type":"standard---start"},"4a7eed":{"name":"Dial","data":{"dial-whom-selector":"user-or-group","dial-whom-user-or-group_id":"'.$this->dial_user_id.'","dial-whom-user-or-group_type":"user","dial-whom-number":"","callerId":"","no-answer-action":"voicemail","no-answer-group-voicemail_say":"","no-answer-group-voicemail_play":"","no-answer-group-voicemail_mode":"","no-answer-group-voicemail_tag":"global","no-answer-group-voicemail_caller_id":"+14158774003","number":"","library":"","no-answer-redirect":"","no-answer-redirect-number":"start/4a7eed/e18b35","version":"3"},"id":"4a7eed","type":"standard---dial"},"e18b35":{"name":"Hangup","data":{},"id":"e18b35","type":"standard---hangup"}}');
		
		$user = VBX_User::get($this->dial_user_id);
				
		ob_start();
		$this->CI->voice('1', '4a7eed');
		$out = ob_get_clean();
	
		// test valid xml
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
	
		// test valid user number item
		$user_name = $user->first_name;
		$user_phone = $user->devices[0]->value;
		$regexp = '|<Number url="http://(.*?)/twiml/whisper\?name='.$user_name.'">\\'.$user_phone.'</Number>|';
		$this->assertRegExp($regexp, $out);
		
		// test no-valid user client item
		$this->assertTrue(preg_match('|<Client>|', $out) === 0);
	}

	// // @todo update this task when the object-cache branch is merged	
	public function testUserDialClient() {
		$this->setFlowVar('data', '{"start":{"name":"Call Start","data":{"next":"start/4a7eed"},"id":"start","type":"standard---start"},"4a7eed":{"name":"Dial","data":{"dial-whom-selector":"user-or-group","dial-whom-user-or-group_id":"'.$this->dial_user_id.'","dial-whom-user-or-group_type":"user","dial-whom-number":"","callerId":"","no-answer-action":"voicemail","no-answer-group-voicemail_say":"","no-answer-group-voicemail_play":"","no-answer-group-voicemail_mode":"","no-answer-group-voicemail_tag":"global","no-answer-group-voicemail_caller_id":"+14158774003","number":"","library":"","no-answer-redirect":"","no-answer-redirect-number":"start/4a7eed/e18b35","version":"3"},"id":"4a7eed","type":"standard---dial"},"e18b35":{"name":"Hangup","data":{},"id":"e18b35","type":"standard---hangup"}}');
		
		$user = VBX_User::get($this->dial_user_id);
		$user->online = 1;
		$user->save();
		
		ob_start();
		$this->CI->voice('1', '4a7eed');
		$out = ob_get_clean();
	
		// test valid xml
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
	
		// test valid user number item
		$user_name = $user->first_name;
		$user_phone = $user->devices[0]->value;
		$regexp = '|<Number url="(.*?)/twiml/whisper\?name='.$user_name.'">\\'.$user_phone.'</Number>|';
		$this->assertRegExp($regexp, $out);
		
		// test valid user client item
		$regexp2 = '|<Client url="(.*?)/twiml/whisper\?name='.$user_name.'">'.$user->id.'</Client>|';
		$this->assertRegExp($regexp2, $out);
	}

	public function testUserDialVoicemail() {
		$this->markTestIncomplete();
	}

	public function testUserDialNoAnswerRedirect() {
		$this->markTestIncomplete();
	}
}