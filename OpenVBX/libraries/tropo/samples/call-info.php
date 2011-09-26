<?php
// Include the library
require('tropo.class.php');

try {
  // If there is not a session object in the POST body,
  // then this isn't a new session. Tropo will throw
  // an exception, so check for that.
  $session = new Session();
} catch (TropoException $e) {
  // This is a normal case, so we don't really need to 
  // do anything if we catch this.
}

$caller = $session->getFrom();

$tropo = new Tropo();
// $caller now has a hash containing the keys: id, name, channel, and network
$tropo->say("Your phone number is " . $caller['id']);

$called = $session->getTo();

// $called now has a hash containing the keys: id, name, channel, and network
$tropo->say("You called " . $called['id'] . " but you probably already knew that.");

if ($called['channel'] == "TEXT") {
  // This is a text message
  $tropo->say("You contacted me via text.");
  
  // The first text of the session is going to be queued and applied to the first
  // ask statement you include...
  $tropo->ask("This will catch the first text", array('choices' => '[ANY]'));

  // ... or, you can grab that first text like this straight from the session.
  $messsage = $tropo->getInitialText();

  $tropo->say("You said " . $message);
} else {
  // This is a phone call
  $tropo->say("Awww. How nice. You cared enough to call.");
}

print $tropo;
?>