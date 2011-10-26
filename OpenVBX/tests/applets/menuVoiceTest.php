<?php

class menuVoiceTest extends OpenVBX_Applet_TestCase {
	public function setUp() {
		parent::setUp();
	}
	
	public function tearDown() {
		parent::tearDown();
	}
	
	public function testMenuSay() {
		$this->markTestIncomplete();
		// test that say verb is present
		// test that repeat value is set
	}
	
	public function testMenuSayReceivedProperDigits() {
		$this->markTestIncomplete();
		// test twiml response to proper digits
	}
	
	public function testMenuSayReceiveImproperDigits() {
		$this->markTestIncomplete();
		// test twiml response to improper digits
	}
	
	public function testMenuSayRedirect() {
		$this->markTestIncomplete();
		// test that the Next dropzone is applied correctly
	}
	
	public function testMenuSayNoRedirect() {
		$this->markTestIncomplete();
		// test that an empty Next dropzone is applied correctly
	}
}