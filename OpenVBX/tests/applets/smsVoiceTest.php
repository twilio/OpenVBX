<?php

/**
 * Tests the SMS Applet in a Voice flow
 *
 */
class smsVoiceTest extends OpenVBX_Applet_TestCase {
	protected $message = 'Greetings, Earthling.';
	protected $redirect_id = 'd2f684';
	
	public function setUp() {
		parent::setUp();
		
		// set up the flow data
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/9c7df4"},"id":"start","type":"standard---start"},"9c7df4":{"name":"Sms","data":{"sms":"'.$this->message.'","next":"start/9c7df4/'.$this->redirect_id.'"},"id":"9c7df4","type":"sms---sms"},"'.$this->redirect_id.'":{"name":"Hangup","data":{},"id":"'.$this->redirect_id.'","type":"standard---hangup"}}',
			'sms_data' => '',
			'tenant_id' => 1	
		));
		
		// set up the request
		$this->setPath('/twiml/applet/voice/1/9c7df4');
		$this->setRequestMethod('POST');
		
		// prepare the header token for request validation
		$this->setRequestToken();
	}
		
	public function testVoiceSms() {
		ob_start();
		$this->CI->voice('1', '9c7df4');
		$out = ob_get_clean();
	
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);

		// Check that our text appears in an SMS reply
		$this->assertRegExp('|(<Sms>'.$this->message.'</Sms>)|', $out);

		// Check that the redirect is present & that ID is correct
		$this->assertEquals(1, preg_match('|(<Redirect>(.*?)/voice/1/'.$this->redirect_id.'</Redirect>)|', $out));
	}
}