<?php
require_once 'tropo.class.php';

// KLogger is a logging class from 
// http://codefury.net/projects/klogger/
require_once 'KLogger.php';

$log = new KLogger ( "log.txt" , KLogger::INFO );

// Does the ?record query string exist? If not, this is an incoming call.
if (!array_key_exists('record', $_GET)) {
  $tropo = new Tropo();
  $tropo->record(array(
    'say' => 'Leave your message at the beep.',
    'url' => getself() . '?record', // append ?record to the URL
    ));
  print $tropo;
} else {
  // Change this path to match the location on your server where you want
  // the file to be saved.
  $target_path = 'path/to/recording/' . $_FILES['filename']['name'];
  if(move_uploaded_file($_FILES['filename']['tmp_name'], $target_path)) {
    $log->LogInfo("$target_path [{$_FILES['filename']['size']} bytes] was saved");
  } else {
    $log->LogError("$target_path could not be saved.");
  }
}

// Simple function to get the full URL of the current script.
function getself() {
 $pageURL = 'http';
 $url = ($_SERVER["HTTPS"] == "on") ? 'https' : 'http';
 $url .= "://" . $_SERVER["SERVER_NAME"];
 $url .= ($_SERVER["SERVER_PORT"] != "80") ? ':'. $_SERVER["SERVER_PORT"] : '';
 $url .= $_SERVER["REQUEST_URI"];
 return $url;
}
?>