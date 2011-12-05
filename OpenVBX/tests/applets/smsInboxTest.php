<?php

class smsInboxTest extends OpenVBX_Applet_TestCase {
	private $message_sid = 'SM12345';
	private $message_from = '4151112222';
	private $message_to = '4153334444';
	private $message_body = 'SMS Body';
	private $message_user = 1;
	private $message_group = 1;
	
	public function setUp() {
		parent::setUp();	
		
		// set up our request
		$this->setPath('/twiml/applet/sms/1/3c8aaf');
		$this->setRequestMethod('POST');
		
		// prepare the header token for request validation
		$this->setRequestToken();

		$this->setFlow(array(
			'id' => 1,
			'user_id' => 1,
			'created' => NULL,
			'updated' => NULL,
			'data' => '',
			'sms_data' => '',
			'tenant_id' => 1
		));	
		
		$this->setRequest(array(
			'SmsSid' => $this->message_sid, 
			'From' => $this->message_from, 
			'To' => $this->message_to, 
			'Body' => $this->message_body
		));				
	}
	
	public function tearDown() {
		$this->CI->db->truncate('group_messages');
		$this->CI->db->truncate('group_annotations');
		$this->CI->db->truncate('user_messages');
		$this->CI->db->truncate('messages');
	}

	public function testUserInboxSave() {
		$this->setFlowVar('sms_data', '{"start":{"name":"Message Received","data":{"next":"start/3c8aaf"},"id":"start","type":"standard---start"},"3c8aaf":{"name":"Sms Inbox","data":{"forward_id":"'.$this->message_user.'","forward_type":"user"},"id":"3c8aaf","type":"sms---sms-inbox"}}');
		
		ob_start();
		$this->CI->sms('1', '3c8aaf');
		$out = ob_get_clean();

		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		
		$vbx_message = new VBX_Message;
		$messages = $vbx_message->get_messages(array(
			'call_sid' => $this->message_sid,
			'user' => array($this->message_user)
		));

		$this->assertEquals(1, count($messages['messages']));
		
		$message = $messages['messages'][0];

		$this->assertEquals($this->message_sid, $message->call_sid);
		$this->assertEquals($this->message_from, $message->caller);
		$this->assertEquals($this->message_to, $message->called);
		$this->assertEquals($this->message_body, $message->content_text);
	}

	public function testGroupInboxSave() {
		$this->setFlowVar('sms_data', '{"start":{"name":"Message Received","data":{"next":"start/3c8aaf"},"id":"start","type":"standard---start"},"3c8aaf":{"name":"Sms Inbox","data":{"forward_id":"'.$this->message_group.'","forward_type":"group"},"id":"3c8aaf","type":"sms---sms-inbox"}}');
		
		ob_start();
		$this->CI->sms('1', '3c8aaf');
		$out = ob_get_clean();
		
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		
		$vbx_message = new VBX_Message;
		$messages = $vbx_message->get_messages(array(
			'call_sid' => $this->message_sid,
			'group' => array($this->message_group)
		));

		$this->assertEquals(1, count($messages['messages']));
		
		$message = $messages['messages'][0];

		$this->assertEquals($this->message_sid, $message->call_sid);
		$this->assertEquals($this->message_from, $message->caller);
		$this->assertEquals($this->message_to, $message->called);
		$this->assertEquals($this->message_body, $message->content_text);
	}

	public function testInvalidSms() {
		$this->setFlowVar('sms_data', '{"start":{"name":"Message Received","data":{"next":"start/3c8aaf"},"id":"start","type":"standard---start"},"3c8aaf":{"name":"Sms Inbox","data":{"forward_id":"'.$this->message_user.'","forward_type":"user"},"id":"3c8aaf","type":"sms---sms-inbox"}}');
				
		$this->setRequest(array(
			'SmsSid' => '', 
			'From' => '4151112222', 
			'To' => '4153334444', 
			'Body' => 'SMS Body Test'
		));
		
		ob_start();
		$this->CI->sms('1', '3c8aaf');
		$out = ob_get_clean();
	
		$xml = simplexml_load_string($out);
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		
		$vbx_message = new VBX_Message;
		$messages = $vbx_message->get_messages(array(
			'call_sid' => $this->message_sid,
			'user' => array($this->message_user)
		));

		$this->assertEquals(0, count($messages['messages']));
	}
}