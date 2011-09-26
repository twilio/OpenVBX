<?php
require_once 'PHPUnit/Framework.php';
require_once 'tropo.class.php';
 
class MessageTest extends PHPUnit_Framework_TestCase
{

    public $partial;
    public $expected;
      
    public function MessageTest() {
      $this->partial = '{"say":{"value":"This is an announcement"},"to":"3055195825","channel":"TEXT","network":"SMS","from":"3055551212","voice":"kate","timeout":10,"answerOnMedia":false,"headers":{"foo":"bar","bling":"baz"}}';
      $this->expected = '{"tropo":[{"message":' . $this->partial . '}]}';
    }

    public function testUseAllOptions()
    {
      $tropo = new Tropo();
      $options = array(
          'to' => "3055195825",
          'from' => "3055551212",
          'network' => "SMS",
          'channel' => "TEXT",
          'answerOnMedia' => false,
          'timeout' => 10,
          'headers' => array('foo'=>'bar','bling'=>'baz'),
          'voice' => 'kate'
        );
      $tropo->message("This is an announcement",$options);        
      $this->assertEquals($this->expected, sprintf($tropo)); 
    }

    public function testUseDifferentOptionsOrder()
    {
      $tropo = new Tropo();
      $options = array(
        'from' => "3055551212",
        'network' => "SMS",
        'timeout' => 10,
        'headers' => array('foo'=>'bar','bling'=>'baz'),
        'channel' => "TEXT",
        'to' => "3055195825",
        'answerOnMedia' => false,
        'voice' => 'kate'
        );
      $tropo->message("This is an announcement",$options);        
      $this->assertEquals($this->expected, sprintf($tropo)); 
    }
}
?>