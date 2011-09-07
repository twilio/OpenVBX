<?php

/*
* fooStack, CIUnit
* Copyright (c) 2008 Clemens Gruenberger
* Released with permission from www.redesignme.com, thanks guys!
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

include_once dirname(__FILE__).'/../CIUnit.php';

class testPHPUnitFeatures extends CIUnit_TestCase {

    function setUp()
    {
        $this->CI = &get_instance();
    }

    /**
    * @dataProvider provider2
    */
    public function testSolve2($a, $b, $c, $res)
    {
        $this->assertEquals($a+$b+$c, $res);
    }

    public function provider2()
    {
        return array(
            array(1, 0, -4, -3),
            array(1, 0, -144, -143),
        );
    }

    /**
    * @expectedException PHPUnit_Framework_Error
    */
    /*
    public function testPhpError()
    {
        $this->assertEquals('PHPUnit_Framework_Error', $this->getExpectedException());
        echo $this->expectedExceptionCode;
        try {
        include 'wrong_file.php';
        }catch(Error $e) {
        print_r($e);
        }
    }*/

}