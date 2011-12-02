<?php

class voicemailUserPlayTest extends OpenVBX_Applet_TestCase {
	private $user_id = 1;
	private $upload_prefix = 'vbx-audio-upload://';
	private $filename = '261d1e265a8c9f8f3683a5452949ea25.mp3';
	
	public function setUp() {
		parent::setUp();
		
		// set the user's voicemail to be a recording
		$this->user = VBX_User::get($this->user_id);
		$this->user->voicemail = $this->upload_prefix.$this->filename;
		$this->user->save();
		
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/59c7d7"},"id":"start","type":"standard---start"},"59c7d7":{"name":"Voicemail","data":{"prompt_say":"","prompt_play":"","prompt_mode":"","prompt_tag":"global","number":"","library":"","permissions_id":"'.$this->user_id.'","permissions_type":"user"},"id":"59c7d7","type":"standard---voicemail"}}',
			'sms_data' => NULL,
			'tenant_id' => 1
		));
		
		// set up our request
		$this->setPath('/twiml/applet/voice/1/59c7d7');
		$this->setRequestMethod('POST');
		
		// prepare the header token for request validation
		$this->setRequestToken();
	}
	
	public function testVoicemailUserPlay() {
		ob_start();
		$this->CI->voice(1, '59c7d7');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);

		$userVmFile = str_replace($this->upload_prefix, '', $this->user->voicemail);
		$this->assertRegExp('|(<Play>(.*?)/audio-uploads/'.$userVmFile.'</Play>)|', $out);
	}
}