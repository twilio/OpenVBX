<?php

class voicemailUserPlayTest extends OpenVBX_Applet_TestCase {
	
	private $filename = 'this-is-not-a-love-song.mp3';
	
	public function setUp() {
		parent::setUp();
		
		// all this is slow, but it properly sets up relationships
		$this->users['user1'] = new VBX_User((object) array(
			'first_name' => 'voicemail',
			'last_name' => 'test1',
			'email' => 'voicemailPlaytest1@openvbx.local',
			'voicemail' => 'vbx-audio-upload://'.$this->filename
		));
		$this->users['user1']->save();
		$this->users['user1']->set_password('password', 'password');
		
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/59c7d7"},"id":"start","type":"standard---start"},"59c7d7":{"name":"Voicemail","data":{"prompt_say":"","prompt_play":"","prompt_mode":"","prompt_tag":"global","number":"","library":"","permissions_id":"'.$this->users['user1']->id.'","permissions_type":"user"},"id":"59c7d7","type":"standard---voicemail"}}',
			'sms_data' => NULL,
			'tenant_id' => 1
		));
		
		// set up our request
		$this->setPath('/twiml/applet/voice/1/59c7d7');
		$this->setRequestMethod('POST');
		
		// prepare the header token for request validation
		$this->setRequestToken();
	}
	
	public function tearDown() {
		$this->CI->db->delete('users', array('id >' => 1));
		$this->users = array();
		
		parent::tearDown();
	}
	
	public function testVoicemailUserPlay() {
		ob_start();
		$this->CI->voice(1, '59c7d7');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);

		$userVmFile = str_replace('vbx-audio-upload://', $this->users['user1']->voicemail);
		$this->assertRegExp('|(<Play>(.*?)'.$this->filename.'</Play>)|', $out);
	}
}