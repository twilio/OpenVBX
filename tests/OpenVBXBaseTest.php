<?php

require_once 'Testing/Selenium.php';
require_once 'PHPUnit/Framework.php';

class OpenVBXBaseTest extends PHPUnit_Framework_TestCase
{
	protected $selenium;
	private $vbx_testing_key;
	private $hostname;
	private $selenium_username;
	private $selenium_password;
	
	private function constructUrl($path)
	{
		$args = http_build_query(compact('vbx_testing_key'));
		$url = $this->webroot . $path . '?'. $args;

		return $url;
	}
	
	public function setUp()
	{
		require_once 'config.php';
		require_once '../OpenVBX/config/openvbx.php';
		var_dump($selenium);
		$this->webroot = $webroot;
		$this->selenium_config = json_encode($selenium);
		$this->vbx_testing_key = $config['testing-key'];
		
		$this->initSelenium();
	}

	protected function initSelenium()
	{
		$this->selenium = new Testing_Selenium($this->selenium_config,
											   $this->webroot,
											   "saucelabs.com",
											   4444,
											   90000);
		$this->selenium->start();
	}
	
	public function tearDown()
	{
		$this->selenium->stop();
	}
}