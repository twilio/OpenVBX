<?php
require_once 'PHPUnit/Framework.php';
require_once 'tropo.class.php';
 
class RecordTest extends PHPUnit_Framework_TestCase
{    
    public function testNewRecordingObject()
    {
      $say = new Say("Please say your account number");
      $choices = new Choices("[5 DIGITS]", NULL, "#");
      $record = new Record(NULL, NULL, true, $choices, NULL, 5, "POST", NULL, NULL, $say, NULL, NULL);
      $this->assertEquals('{"beep":true,"choices":{"value":"[5 DIGITS]","termChar":"#"},"maxSilence":5,"method":"POST","say":{"value":"Please say your account number"}}',  sprintf($record)); 
    }
    
    public function testRecordUsingObject()
    {
      $say = new Say("Please say your account number");
      $choices = new Choices("[5 DIGITS]", NULL, "#");
      $record = new Record(NULL, NULL, true, $choices, NULL, 5, "POST", NULL, NULL, $say, NULL, NULL);
      $tropo = new Tropo();
      $tropo->Record($record);
      $this->assertEquals('{"tropo":[{"record":{"beep":true,"choices":{"value":"[5 DIGITS]","termChar":"#"},"maxSilence":5,"method":"POST","say":{"value":"Please say your account number"}}}]}',  sprintf($tropo)); 
    }
    
    public function testRecordUsingTropoMethod()
    {
      $say = new Say("Please say your account number");
      $choices = new Choices("[5 DIGITS]", NULL, "#");
      $tropo = new Tropo();
      $tropo->Record(NULL, NULL, true, $choices, NULL, 5, "POST", NULL, NULL, $say, NULL, NULL);
      $this->assertEquals('{"tropo":[{"record":{"beep":true,"choices":{"value":"[5 DIGITS]","termChar":"#"},"maxSilence":5,"method":"POST","say":{"value":"Please say your account number"}}}]}',  sprintf($tropo)); 
    }
    
    public function testRecordTranscription()
    {
      $say = new Say("Please say your account number");
      $choices = new Choices("[5 DIGITS]", NULL, "#");
      $transcription = new Transcription('http://example.com/', 'bling', 'encoded');
      $tropo = new Tropo();
      $tropo->Record(NULL, NULL, true, $choices, NULL, 5, "POST", NULL, NULL, $say, NULL, NULL,$transcription);
      $this->assertEquals('{"tropo":[{"record":{"beep":true,"choices":{"value":"[5 DIGITS]","termChar":"#"},"maxSilence":5,"method":"POST","say":{"value":"Please say your account number"},"transcription":{"id":"bling","url":"http://example.com/","emailFormat":"encoded"}}}]}',  sprintf($tropo)); 
    }
    
}
?>