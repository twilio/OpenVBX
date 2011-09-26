<?php
require_once 'PHPUnit/Framework.php';
require_once 'tropo.class.php';
 
class HangupTest extends PHPUnit_Framework_TestCase
{

    public function testHangup()
    {
      $tropo = new Tropo();
      $tropo->Hangup();
      $this->assertEquals('{"tropo":[{"hangup":"null"}]}', sprintf($tropo));
    }
    
}
?>