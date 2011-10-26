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
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/a9afb"},"id":"start","type":"standard---start"},"a9afb":{"name":"Greeting","data":{"prompt_say":"'.$this->prompt.'","prompt_play":"","prompt_mode":"say","prompt_tag":"global","number":"","library":"","next":""},"id":"a9afb","type":"standard---greeting"}}',
			'sms_data' => NULL,
			'tenant_id' => 1
		));		
	}
	
	public function testGreetingTwiml() {
		ob_start();
		$this->CI->voice(1, 'a9afb');
		$out = ob_get_clean();
		
		$xml = simplexml_load_string($out);
		$this->assertEquals('SimpleXMLElement', get_class($xml));
		
		$this->assertRegexp('|<Say>'.$this->prompt.'</Say>|', $out);
	}
	
	public function testGreetingRedirect() {
		$this->markTestIncomplete();
	}
}
