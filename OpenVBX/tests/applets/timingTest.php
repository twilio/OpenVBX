<?php

class timingTest extends OpenVBX_Applet_TestCase {
	protected $open_id = 'f01d30';
	protected $closed_id = '4b01a7';
	
	public function setUp() {
		parent::setUp();
		
		// set up our request
		$this->setPath('/twiml/applet/voice/1/854578');
		$this->setRequestMethod('POST');
		
		// prepare the header token for request validation
		$this->setRequestToken();
	}
	
	protected function setupFlowTestData($open, $close) {
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/854578"},"id":"start","type":"standard---start"},"854578":{"name":"Timing","data":{"range_0_from":"'.$open.'","range_0_to":"'.$close.'","range_1_from":"'.$open.'","range_1_to":"'.$close.'","range_2_from":"'.$open.'","range_2_to":"'.$close.'","range_3_from":"'.$open.'","range_3_to":"'.$close.'","range_4_from":"'.$open.'","range_4_to":"'.$close.'","range_5_from":"'.$open.'","range_5_to":"'.$close.'","range_6_from":"'.$open.'","range_6_to":"'.$close.'","open":"start/854578/'.$this->open_id.'","closed":"start/854578/'.$this->closed_id.'"},"id":"854578","type":"timing---timing"},"'.$this->open_id.'":{"name":"Hangup","data":{},"id":"'.$this->open_id.'","type":"standard---hangup"},"'.$this->closed_id.'":{"name":"Hangup","data":{},"id":"'.$this->closed_id.'","type":"standard---hangup"}}',
			'sms_data' => NULL,
			'tenant_id' => 1
		));
	}
	
	/**
	 * Sets the range in the TwiML so that it encompasses the current time
	 * This might run in to issues around midnight... but, seriously, who
	 * actually tests at midnight. That's hack time!
	 *
	 * @return void
	 */
	public function testTimingOpen() {	
		$open = date_create('now -5 minutes');
		$close = date_create('now +5 minutes');
		$this->setupFlowTestData($open->format('h:i A'), $close->format('h:i A'));
		
		ob_start();
		$this->CI->voice('1', '854578');
		$out = ob_get_clean();
			
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);

		$this->assertRegExp('|<Redirect>(.*?)/twiml/applet/voice/1/'.$this->open_id.'</Redirect>|', $out);
	}
	
	/**
	 * Sets the range in the TwiML so that it is beyond the current time
	 *
	 * @return void
	 */
	public function testTimingClosed() {
		$open = date_create('now +5 minutes');
		$close = date_create('now +10 minutes');
		$this->setupFlowTestData($open->format('h:i A'), $close->format('h:i A'));

		ob_start();
		$this->CI->voice('1', '854578');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);

		$this->assertRegExp('|<Redirect>(.*?)/twiml/applet/voice/1/'.$this->closed_id.'</Redirect>|', $out);
	}
}