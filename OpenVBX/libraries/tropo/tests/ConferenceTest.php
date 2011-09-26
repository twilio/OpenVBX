<?php
require_once 'PHPUnit/Framework.php';
require_once 'tropo.class.php';
 
class ConferenceTest extends PHPUnit_Framework_TestCase
{
    public $partial;
    public $expected;
        
    public function ConferenceTest() {
      $this->partial = '{"id":1234,"mute":false,"name":"foo","playTones":false,"terminator":"#"}';
      $this->expected = '{"tropo":[{"conference":' . $this->partial . '}]}';
    }
    
    public function testCreateConferenceObject()
    {
      $conference = new Conference(1234, false, "foo", NULL, false, NULL, "#");
      $this->assertEquals($this->partial, sprintf($conference));
    }


    public function testConferenceFromObject()
    {
      $conference = new Conference(1234, false, "foo", NULL, false, NULL, "#");
      $tropo = new Tropo();
      $tropo->Conference($conference);
      $this->assertEquals($this->expected, sprintf($tropo));
    }
    
    public function testConferenceWithOptions()
    {
      $options = array(
          'id' => 1234,
          'mute' => 'false',
          'name' => 'foo',
          'playTones' => false,
          'terminator' => '#'
        );
      $tropo = new Tropo();
      $tropo->Conference($options);
      $this->assertEquals($this->expected, sprintf($tropo));
    }
    
    public function testConferenceWithOptionsInDifferentOrder()
    {
      $options = array(
        'terminator' => '#',
        'playTones' => false,
        'id' => 1234,
        'mute' => 'false',
        'name' => 'foo'
        );
      $tropo = new Tropo();
      $tropo->Conference($options);
      $this->assertEquals($this->expected, sprintf($tropo));
    }
    
    public function testConferenceWithOnHandler() {
      $say = new Say('Welcome to the conference. Press the pound key to exit.');
      // Set up an On object to handle the event.
      // Note - statically calling the properties of the Event object can be used 
      //   as the first parameter to the On Object constructor.
      $on = new On(Event::$join, NULL, $say);
      $options = array(
        'id' => 1234,
        'mute' => 'false',
        'terminator' => '#',
        'playTones' => false,
        'name' => 'foo',
        'on' => $on
        );
      $tropo = new Tropo();
      $tropo->Conference($options);
      $this->assertEquals('{"tropo":[{"conference":{"id":1234,"mute":false,"on":{"event":"join","say":{"value":"Welcome to the conference. Press the pound key to exit."},"name":"foo","playTones":false,"terminator":"#"}}]}', sprintf($tropo));
    }
}
?>