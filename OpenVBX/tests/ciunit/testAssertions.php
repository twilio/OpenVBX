<?php

/*
* fooStack, CIUnit
* Copyright (c) 2008 Clemens Gruenberger
* Released with permission from www.redesignme.com, thanks guys!
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

include_once dirname(__FILE__).'/../CIUnit.php';

class testAssertions extends CIUnit_TestCase{

    function setUp(){
    }

    public function testAssertContaining(){
       $this->assertContaining(array('foo'=>'bar'), array('me', 'you'=>'too', 'foo'=>'bar', 'and something else'));
       //$this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
    }

    public function testAssertDifferenceOfAction(){
       $this->assertDifferenceOfAction(1, 0, 'we do something here', 1);
    }

    public function testAssertDifference(){
       $this->assertDifference(1, 6, 7);
    }

    /*public function testAssertContainsError(){
      /* FIXME, use a MOCK object 
       $this->CI->v->set_fields('var', 'varname');
       $this->CI->v->set_rules('var', 'required');
       $this->CI->v->run(array());
       $this->assertContainsError('var', $this->CI->v);
    }*/

    public function testAssertCounts(){
       $this->assertCounts(4, array('me', 'you'=>'too', 'foo'=>'bar', 'and something else'));
    }

}