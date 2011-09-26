<?php
// To make an outgoing call, simply add your
// Tropo application token and the number you
// want to dial to the settings below. Then 
// put this on your web server and load it
// in your browser.

// Settings! Shiny.
$token = 'your token here';
$number = 'the number you would like to dial';

// Include the Tropo library and create a Tropo object
include_once 'tropo.class.php';
$tropo = new Tropo();

// When the the session object is created, it tries
// to load the json that Tropo posts when reciving or
// making a call. If the json doesn't exist, the 
// Session object throws a TropoException.
// This try/catch block checks to see if this code is
// being run as part of a session or being run directly.
try {
  
  // this next line throws an exception if the code isn't
  // being run by Tropo. If that happens, the catch block
  // below will run.
  $session = new Session();
  
  if ($session->getParameters("action") == "create") {  
    // A token-launched session (an outgoing call) will
    // have a parameter called "action" that is set to
    // "create". If this is true, we're trying to make an
    // outgoing call. The next two lines make that call
    // and say something.
    $tropo->call($session->getParameters("dial"));
    $tropo->say('This is an outbound call.');
  } else {
    
    // The session JSON exists, but there's no action 
    // parameter or it wasn't set to "create" so this must 
    // be an incoming call.
    $tropo->say('Thank you for calling us.');
  }
  $tropo->renderJSON();
} catch (TropoException $e) {
  if ($e->getCode() == '1') {
    
    // The session object threw an exception, so this file wasn't
    // loaded as part of a Tropo session. Use the session API to 
    // launch a new session.
    if ($tropo->createSession($token, array('dial' => $number))) {
      print 'Call launched to ' . $number;
    } else {
      print "call failed! Try it again with the Tropo debugger running to see what the error is.";
    }
  }
}
?>
