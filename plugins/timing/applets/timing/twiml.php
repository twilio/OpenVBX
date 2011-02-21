<?php
$response = new Response();
$ini = "$this->plugin_path/timing.ini";
if (is_readable($ini)) {
	$options = parse_ini_file($ini);
	try {
		$tz = new DateTimeZone($options['timezone']);
	} catch (Exception $e) {
		error_log($e->getMessage());
	}
}
if (empty($tz)) $tz = new DateTimeZone('America/Los_Angeles');
$now = date_create('now', $tz); // TODO: get from config
$today = date_format($now, 'w') - 1;
$response->addRedirect(AppletInstance::getDropZoneUrl(
  ($from = AppletInstance::getValue("range_{$today}_from"))
  && ($to = AppletInstance::getValue("range_{$today}_to"))
  && date_create($from, $tz) <= $now && $now < date_create($to, $tz)
  ? 'open'
  : 'closed'
));
$response->Respond();
