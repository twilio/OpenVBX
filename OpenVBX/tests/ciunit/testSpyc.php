<?php

/*
* fooStack, CIUnit
* Copyright (c) 2008 Clemens Gruenberger
* Released with permission from www.redesignme.com, thanks guys!
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

include_once dirname(__FILE__) . '/../CIUnit.php';

class testSpyc extends PHPUnit_Framework_TestCase {

    protected function setUp()
    {
        $this->CI = &set_controller();
        $this->CI->load->library('Spyc');
    }

    /*
    *test loading of Spyc YAML Library
    */
    public function testSpycLibraryLoading()
    {
        $this->assertTrue(method_exists($this->CI->spyc, 'YAMLLoad'));
    }

    /*
    * Test loading of YAML file
    */
    public function testLoadYaml()
    {
      $yaml = Spyc::YAMLLoad(FSPATH . 'Spyc/spyc.yaml');
      $yaml2 = $this->CI->spyc->YAMLLoad(FSPATH . 'Spyc/spyc.yaml');
      $this->assertEquals($yaml, $yaml2);

      $this->assertInternalType('array', $yaml); // changed assertType to assetInternalType per PHPUnit 3.6 requirements
      $testfile = file_get_contents(FSPATH . 'Spyc/test.php');
      $this->assertSame(
        1,
        preg_match('/YAMLLoad\(\'spyc\.yaml\'\);([\s\S]*)\?>/', $testfile, $matches)
      );

      ob_start();
        eval($matches[1]);
        $testresults = ob_get_contents();
      ob_end_clean();
      $this->assertSame("spyc.yaml parsed correctly\n", $testresults); 
    }

}