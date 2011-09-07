<?php
ob_start();

if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AppletsAllTests::main');
}

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once dirname(__FILE__).'/../CIUnit.php';
require_once dirname(__FILE__).'/../OpenVBX.php';
require_once dirname(__FILE__).'/../OpenVBX_Applet.php';

class AppletsAllTests extends CIUnitTestSuite 
{	
	public static function suite()
	{
		$files = CIUnit::files('/.*Test\.php/', dirname(__FILE__));
		$suite = new PHPUnit_Framework_TestSuite('Applet tests');
		foreach($files	as $file){
			require_once($file);
			$file = str_replace('.php', '', $file);
			$suite->addTestSuite($file);
		}
		return $suite;
	}	
}

if (PHPUnit_MAIN_METHOD == 'AppletsAllTests::main') {
	AppletsAllTests::main();
}

while (@ob_end_clean());