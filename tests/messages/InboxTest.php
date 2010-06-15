<?php

require_once 'OpenVBXBaseTest.php';

class InboxTest extends OpenVBXBaseTest
{
	function testCallback()
	{

		$hiccup = json_encode(array('type' => 'rest',
									'http-status' => 200,
									'response' => '<TwilioResponse>
	<Call>
		<Sid>CA42ed11f93dc08b952027ffbc406d0868</Sid>
		<CallSegmentSid/>
		<AccountSid>AC309475e5fede1b49e100272a8640f438</AccountSid>
		<Called>4155551212</Called>
		<Caller>4158675309</Caller>
		<PhoneNumberSid>PN01234567890123456789012345678900</PhoneNumberSid>
		<Status>0</Status>
		<StartTime>Thu, 03 Apr 2008 04:36:33 -0400</StartTime>
		<EndTime/>
		<Price/>
		<Flags>1</Flags>
	</Call></TwilioResponse>'));
		
		$selenium = $this->selenium;
		$selenium->open("/auth/login");
		$selenium->type("iEmail", "adam@twilio.com");
		$selenium->type("iPass", "u2uqHlDzV0");
		$selenium->click("//button[@type='submit']");
		$selenium->waitForPageToLoad("30000");
		$selenium->addCustomRequestHeader(array('Hiccup-Config: '.json_encode($hiccup)));
		$selenium->click("link=(480) 334-2609");
		$selenium->click("link=Call(480) 334-2609 (415) 367-3138+14152341234");
	}
}