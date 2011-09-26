<?php
require_once 'PHPUnit/Framework.php';
require_once 'tropo.class.php';
 
class StartRecordingTest extends PHPUnit_Framework_TestCase
{    
    public function testNewRecordingObject()
    {
        $recording = new StartRecording('recording','audio/mp3','POST','password','http://blah.com/recordings/1234.wav','jose');
        $this->assertEquals('{"format":"audio/mp3","method":"POST","password":"password","url":"http://blah.com/recordings/1234.wav","username":"jose"}',  sprintf($recording)); 
        $tropo = new Tropo();
        $tropo->StartRecording($recording);
        $this->assertEquals('{"tropo":[{"startRecording":{"format":"audio/mp3","method":"POST","password":"password","url":"http://blah.com/recordings/1234.wav","username":"jose"}}]}',  sprintf($tropo)); 
    }
    
    public function testStartRecordingWithParameters()
    {
        $tropo = new Tropo();
        $tropo->StartRecording('recording','audio/mp3','POST','password','http://blah.com/recordings/1234.wav','jose');
        $this->assertEquals('{"tropo":[{"recording":{"format":"audio/mp3","method":"POST","password":"password","url":"http://blah.com/recordings/1234.wav","username":"jose"}}]}',  sprintf($tropo)); 
    }
}
?>