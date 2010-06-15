<?php
require_once 'OpenVBXBaseTest.php';

class AuthTest extends OpenVBXBaseTest
{
	public function setUp()
	{
		parent::setUp();
	}
	
	public function testAuthentication()
	{
		$this->selenium->open("/auth/login");
		$this->assertEquals("Log In | VBX",
							$this->selenium->getTitle());
		$this->selenium->type("email", "adam@twilio.com");
		$this->selenium->type("pw", "u2uqHlDzV0");
		$this->selenium->click("//button[@type='submit']");
		$this->selenium->waitForPageToLoad("30000");
		$this->assertEquals('Messages - Inbox | VBX',
							$this->selenium->getTitle());
	}

}