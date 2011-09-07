<?php

/*
* fooStack, CIUnit
* Copyright (c) 2008 Clemens Gruenberger
* Released with permission from www.redesignme.com, thanks guys!
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'ControllersAllTests::main');
}

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once dirname(__FILE__).'/../CIUnit.php';
$files = CIUnit::files('/test.*\.php/', dirname(__FILE__), true);
foreach($files as $file){
    require_once $file;
}


class HelpersAllTests extends CIUnitTestSuite{

    public static function suite()
    {
        $files = CIUnit::files('/test.*\.php/', dirname(__FILE__));
        $suite = new PHPUnit_Framework_TestSuite('Helper tests');
        foreach($files  as $file){
            $file = str_replace('.php', '', $file);
            $suite->addTestSuite($file);
        }
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'HelpersAllTests::main') {
    HelpersAllTests::main();
}