<?php

// **OpenVBX Note:**
// - PHP 5.3 required to run tests.
// - It is strongly recommended that you run AllTests
//   No care has been taken to ensure that individual 
//   tests or suites will run properly on their own.

if (version_compare(phpversion(), '5.3', '<')) {
	echo PHP_EOL.'***'.PHP_EOL.
		'*** ERROR:'.PHP_EOL.
		'*** PHP Version 5.3 is required to run OpenVBX Integration tests.'.PHP_EOL.
		'*** PHP Version `'.phpversion().'` detected.'.PHP_EOL. 
		'***'.PHP_EOL.PHP_EOL;
	exit;
}

// **OpenVBX Note:**
// To output a test coverage report you'll need to bump the ram
// You'll probably also need a beer, too.
// ini_set('memory_limit', '1024M');

// **OpenVBX Note:**
// It is recommended that you export your Twilio Sid & Token as environment
// variables. If you haven't, or can't, hard code your Sid & Token here for
// the setup of a complete testing environment
define('TWILIO_SID', getenv('TWILIO_SID'));
define('TWILIO_TOKEN', getenv('TWILIO_TOKEN'));

if ((!defined('TWILIO_SID') || strlen(TWILIO_SID) < 32) || (!defined('TWILIO_TOKEN') || strlen(TWILIO_TOKEN) < 32)) {
	echo PHP_EOL.'***'.PHP_EOL.
		'*** Error:'.PHP_EOL.
		'*** TWILIO_SID and TWILIO_TOKEN must be correctly defined.'.PHP_EOL.
		'*** Current values:'.PHP_EOL.
		'*** - TWILIO_SID: '.TWILIO_SID.PHP_EOL.
		'*** - TWILIO_TOKEN: '.TWILIO_TOKEN.PHP_EOL.
		'***'.PHP_EOL.PHP_EOL;
		exit;
}

/*
* fooStack, CIUnit for CodeIgniter
* Copyright (c) 2008-2009 Clemens Gruenberger
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

if (!defined('PHPUnit_MAIN_METHOD'))
{
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

// set up a fresh database install on each run
require_once('databaseConfig.php');

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'controllers/ControllersAllTests.php';
require_once 'models/ModelsAllTests.php';
require_once 'views/ViewsAllTests.php';
require_once 'libs/LibsAllTests.php';
require_once 'helpers/HelpersAllTests.php';
require_once 'system/SystemAllTests.php';
require_once 'ciunit/CiunitAllTests.php';
require_once 'applets/AppletsAllTests.php';

class AllTests extends CIUnitTestSuite {

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('APPLICATION testsuite');

        //CUnit tests itself
        $suite->addTestSuite('CiunitAllTests');

        //test here your system setup, e.g. for
        //env variables, php version, etc.
        $suite->addTestSuite('SystemAllTests');

        //test CI framework libs and extensions
        $suite->addTestSuite('LibsAlltests');

        //test your application
        $suite->addTestSuite('ModelsAlltests');
        $suite->addTestSuite('ViewsAlltests');
        $suite->addTestSuite('ControllersAlltests');
        $suite->addTestSuite('HelpersAlltests');

		// test OpenVBX
		$suite->addTestSuite('AppletsAllTests');

        return $suite;
    }

}

if (PHPUnit_MAIN_METHOD == 'AllTests::main')
{
    AllTests::main();
}