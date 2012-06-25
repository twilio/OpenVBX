<?php
$response = new TwimlResponse;

$now = date_create('now');
$today = date_format($now, 'w') - 1;

// Stored in the database as range_0_from ... range_6_from
// 0 == Monday, 6 == Sunday. 
// We need to add this logic to "loop around" back to 6 instead
// of using "-1" for sunday.
if ($today == -1) { $today = 6; }

$response->redirect(AppletInstance::getDropZoneUrl(
  ($from = AppletInstance::getValue("range_{$today}_from"))
  && ($to = AppletInstance::getValue("range_{$today}_to"))
  && date_create($from) <= $now && $now < date_create($to)
  ? 'open'
  : 'closed'
));

$response->respond();
