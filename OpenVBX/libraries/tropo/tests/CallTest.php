<?php
require_once 'PHPUnit/Framework.php';
require_once 'tropo.class.php';
 
class CallTest extends PHPUnit_Framework_TestCase
{

    public function testToOnly()
    {
      $tropo = new Tropo();
      $tropo->call("3055195825");
      $this->assertEquals('{"tropo":[{"call":{"to":"3055195825"}}]}', sprintf($tropo));
    }
    
    public function testUseAllOptions()
    {
      $tropo = new Tropo();
      $rec = new StartRecording('recording','audio/mp3','POST','password','http://blah.com/recordings/1234.wav','jose');
      $options = array(
          'from' => "3055551212",
          'network' => "SMS",
          'channel' => "TEXT",
          'answerOnMedia' => false,
          'timeout' => 10,
          'headers' => array('foo'=>'bar','bling'=>'baz'),
          'recording' => $rec
        );
      $tropo->call("3055195825",$options);        
      $this->assertEquals('{"tropo":[{"call":{"to":"3055195825","from":"3055551212","network":"SMS","channel":"TEXT","timeout":10,"answerOnMedia":false,"headers":{"foo":"bar","bling":"baz"},"recording":{"format":"audio/mp3","method":"POST","password":"password","url":"http://blah.com/recordings/1234.wav","username":"jose"}}}]}',  sprintf($tropo)); 
    }

    public function testUseDifferentOptionsOrder()
    {
      $tropo = new Tropo();
      $rec = new StartRecording('recording','audio/mp3','POST','password','http://blah.com/recordings/1234.wav','jose');
      $options = array(
          'answerOnMedia' => false,
          'timeout' => 10,
          'network' => "SMS",
          'channel' => "TEXT",
          'headers' => array('foo'=>'bar','bling'=>'baz'),
          'recording' => $rec,
          'from' => "3055551212"
        );
      $tropo->call("3055195825",$options);        
      $this->assertEquals('{"tropo":[{"call":{"to":"3055195825","from":"3055551212","network":"SMS","channel":"TEXT","timeout":10,"answerOnMedia":false,"headers":{"foo":"bar","bling":"baz"},"recording":{"format":"audio/mp3","method":"POST","password":"password","url":"http://blah.com/recordings/1234.wav","username":"jose"}}}]}',  sprintf($tropo)); 
    }

    public function testCreateCallObject()
    {
      $rec = new StartRecording('recording','audio/mp3','POST','password','http://blah.com/recordings/1234.wav','jose');
      $call = new Call("3055195825","3055551212","SMS","TEXT",false,10,array('foo'=>'bar','bling'=>'baz'),$rec);
      $this->assertEquals('{"to":"3055195825","from":"3055551212","network":"SMS","channel":"TEXT","timeout":10,"answerOnMedia":false,"headers":{"foo":"bar","bling":"baz"},"recording":{"format":"audio/mp3","method":"POST","password":"password","url":"http://blah.com/recordings/1234.wav","username":"jose"}}',  sprintf($call)); 
    }

    public function testCallUsingCallObject()
    {
      $tropo = new Tropo();
      $rec = new StartRecording('recording','audio/mp3','POST','password','http://blah.com/recordings/1234.wav','jose');
      $call = new Call("3055195825","3055551212","SMS","TEXT",false,10,array('foo'=>'bar','bling'=>'baz'),$rec);
      $tropo->call($call);        
      $this->assertEquals('{"tropo":[{"call":{"to":"3055195825","from":"3055551212","network":"SMS","channel":"TEXT","timeout":10,"answerOnMedia":false,"headers":{"foo":"bar","bling":"baz"},"recording":{"format":"audio/mp3","method":"POST","password":"password","url":"http://blah.com/recordings/1234.wav","username":"jose"}}}]}',  sprintf($tropo)); 
    }

}
?>