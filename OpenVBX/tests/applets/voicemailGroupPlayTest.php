<?php

class voicemailGroupPlayTest extends OpenVBX_Applet_TestCase
{
	private $group_id = 1;
	private $filename = 'this-is-not-a-love-song.mp3';
	
	public function setUp()
	{
		parent::setUp();
		
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/f274cd"},"id":"start","type":"standard---start"},"f274cd":{"name":"Voicemail","data":{"prompt_say":"","prompt_play":"vbx-audio-upload://this-is-not-a-love-song.mp3","prompt_mode":"play","prompt_tag":"global","number":"","library":"vbx-audio-upload://'.$this->filename.'","permissions_id":"'.$this->group_id.'","permissions_type":"group"},"id":"2fdfb8","type":"standard---voicemail"}}',
			'sms_data' => NULL,
			'tenant_id' => 1
		));
		
		// set up our request
		$this->setPath('/twiml/applet/voice/1/f1e974');
		$this->setRequestMethod('POST');
		
		// prepare the header token for request validation
		$this->setRequestToken();
	}
	
	public function tearDown() 
	{
		$this->CI->db->truncate('group_messages');
		$this->CI->db->truncate('group_annotations');
		$this->CI->db->truncate('user_messages');
		$this->CI->db->truncate('messages');		
		parent::tearDown();
	}

	public function testVoicemailGroupPlay()
	{
		ob_start();
		$this->CI->voice(1, 'f274cd');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);

		$this->assertRegExp('|(<Play>(.*?)/audio-uploads/'.$this->filename.'</Play>)|', $out);
	}
	
	public function testVoicemailGroupTranscribeCallback()
	{
		ob_start();
		$this->CI->voice(1, 'f274cd');
		$out = ob_get_clean();
		
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		
		$this->assertRegExp('|(<Record transcribeCallback="(.*?)/twiml/transcribe"/>)|', $out);
	}	
}