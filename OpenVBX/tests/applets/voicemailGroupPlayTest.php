<?php

include_once dirname(__FILE__).'/../CIUnit.php';

class voicemailGroupPlayTest extends OpenVBX_Applet_TestCase
{
	private $users = array();
	private $group = null;
	
	private $filename = 'this-is-not-a-love-song.mp3';
	
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
			'data' => '{"start":{"name":"Call Start","data":{"next":"start/f274cd"},"id":"start","type":"standard---start"},"f274cd":{"name":"Voicemail","data":{"prompt_say":"","prompt_play":"vbx-audio-upload://this-is-not-a-love-song.mp3","prompt_mode":"play","prompt_tag":"global","number":"","library":"vbx-audio-upload://'.$this->filename.'","permissions_id":"'.$this->group->id.'","permissions_type":"group"},"id":"2fdfb8","type":"standard---voicemail"}}',
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

	public function testVoicemailGroupPlay()
	{
		ob_start();
		$this->CI->voice(1, 'f274cd');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);

		$this->assertRegExp('|(<Play>(.*?)'.$this->filename.'</Play>)|', $out);
	}	
}