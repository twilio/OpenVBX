<?php

class greetingTest extends OpenVBX_Applet_TestCase
{
	private $prompt = 'Greetings earthling!';

	public function setUp() 
	{
		parent::setUp();
		
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/777eca"},"id":"start","type":"standard---start"},"e18b35":{"name":"Hangup","data":{},"id":"e18b35","type":"standard---hangup"},"777eca":{"name":"Greeting","data":{"prompt_say":"'.$this->prompt.'","prompt_play":"","prompt_mode":"say","prompt_tag":"global","prompt_caller_id":"+14158774003","number":"","library":"","next":"start/777eca/db4f8d"},"id":"777eca","type":"standard---greeting"},"db4f8d":{"name":"Hangup","data":{},"id":"db4f8d","type":"standard---hangup"}}',
			'sms_data' => NULL,
			'tenant_id' => 1
		));		
	}
	
	public function testGreetingTwiml() {
		ob_start();
		$this->CI->voice(1, '777eca');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		
		// this regex match is cheap, need better reg-fu to match possible
		// language and voice attributes that could appear in any order
		$this->assertRegexp('|<Say(.*?)>'.$this->prompt.'</Say>|', $out);
		
		// test redirect
		$this->assertRegexp('|<Redirect>(.*?)/twiml/applet/voice/1/([0-9a-z]*)</Redirect>|', $out);
	}
}
