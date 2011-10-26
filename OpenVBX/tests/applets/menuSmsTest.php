<?php

class menuSmsTest extends OpenVBX_Applet_TestCase {
	public function setUp() {
		parent::setUp();
	}
	
	public function tearDown() {
		parent::tearDown();
	}
	
	public function testSmsInitialResponse() {
		$this->markTestIncomplete();
		// resond with instructions
	}
	
	public function testSmsRespondProperKeyword() {
		$this->markTestIncomplete();
		// respond with text
	}
	
	public function testSmsRespondImproperKeyword() {
		$this->markTestIncomplete();
		// no valid keyword, no reponse
	}
}