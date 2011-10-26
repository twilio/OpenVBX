<?php

/*
* fooStack, CIUnit
* Copyright (c) 2008 Clemens Gruenberger
* Released with permission from www.redesignme.com, thanks guys!
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

include_once dirname(__FILE__).'/../CIUnit.php';

class testPhp extends CIUnit_TestCase{

    function setUp(){
    }

    public function testFunctionJsonEncode(){
        $this->assertTrue(function_exists('json_encode'));
    }

    public function testPhpVersion(){
        $this->assertTrue(phpversion() > '5.1');
    }

}