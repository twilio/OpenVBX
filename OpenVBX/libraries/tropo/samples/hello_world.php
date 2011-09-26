<?php
/**
 * A sample application that demonstrates the use of the TropoPHP packeage.
 * @copyright 2010 Mark J. Headd (http://www.voiceingov.org)
 */

// Include Tropo classes.
require('tropo.class.php');

$tropo = new Tropo();
$tropo->say("Hello World!");
$tropo->renderJson();

?>