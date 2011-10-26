<?php

include_once dirname(__FILE__).'/../CIUnit.php';

class voicemailGroupSayTest extends OpenVBX_Applet_TestCase
{
	private $users = array();
	private $group = null;
	
	public function setUp()
	{
		parent::setUp();
		
		// all this is slow, but it properly sets up relationships
		$this->users['user1'] = new VBX_User((object) array(
			'first_name' => 'voicemail',
			'last_name' => 'test1',
			'email' => 'voicemailtest1@openvbx.local',
			'voicemail' => 'Voicemail user 1 no home'
		));
		$this->users['user1']->save();
		$this->users['user1']->set_password('password', 'password');
	
		$this->users['user2'] = new VBX_User((object) array(
			'first_name' => 'voicemail',
			'last_name' => 'test2',
			'email' => 'voicemailtest2@openvbx.local',
			'voicemail' => 'Voicemail user 2 no home'
		));
		$this->users['user2']->save();
		$this->users['user2']->set_password('password', 'password');		
		
		// insert a known group to test against
		$this->group = new VBX_Group((object) array(
			'name' => 'Test Voicemail Group',
			'tenant_id' => 1,
			'is_active' => 1
		));
		$this->group->save();
		
		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/f274cd"},"id":"start","type":"standard---start"},"f274cd":{"name":"Voicemail","data":{"prompt_say":"I am group email. Please leave a message.","prompt_play":"","prompt_mode":"say","prompt_tag":"global","number":"","library":"","permissions_id":"'.$this->group->id.'","permissions_type":"group"},"id":"f274cd","type":"standard---voicemail"}}',
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
		$this->group->remove_user($user1->id);
		$this->group->remove_user($user2->id);
		
		$this->CI->db->truncate('group_messages');
		$this->CI->db->truncate('group_annotations');
		$this->CI->db->truncate('user_messages');
		$this->CI->db->truncate('messages');
		$this->CI->db->delete('users', array('id >' => 1));
		$this->CI->db->delete('groups', array('id >' => 2));
		
		$this->users = array();
		$this->group = null;
		
		parent::tearDown();
	}

	public function testVoicemailGroupPrompt()
	{
		ob_start();
		$this->CI->voice(1, 'f274cd');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertEquals('SimpleXMLElement', get_class($xml));

		$this->assertRegExp('|(<Say>I am group email. Please leave a message.</Say>)|', $out);
	}
	
	public function testVoicemailGroupMessage()
	{
		$message_data = array(
			'CallSid' => 'CA123',
			'From' => '+15148675309',
			'To' => '+13038675309',
			'RecordingUrl' => 'http://foo.com/my/group_recording.wav',
			'RecordingDuration' => 10
		);
		$this->setRequest($message_data);	

		ob_start();
		$this->CI->voice(1, 'f274cd');
		$out = ob_get_clean();

		$result = $this->CI->db->get_where('messages', array('content_url' => $message_data['RecordingUrl']))->result();
		$this->assertEquals(1, count($result));
		
		$message = current($result);
		$this->assertEquals($message_data['CallSid'], $message->call_sid);
		$this->assertEquals($message_data['From'], $message->caller);
		$this->assertEquals($message_data['To'], $message->called);
		$this->assertEquals($message_data['RecordingUrl'], $message->content_url);
		$this->assertEquals($message_data['RecordingDuration'], $message->size);
	}
}