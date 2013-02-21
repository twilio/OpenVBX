<?php
$response = new TwimlResponse;

$now = date_create('now');
$today = date_format($now, 'N') - 1;

/**
 * The names of the applet instance variables for "from" and "to" times
 * are of the form: "range_n_from" and "range_n_to" where "n"
 * is a value between 0 and 6 (inclusive). 0 represents Monday
 * and 6 represents Sunday. In PHP, the value of date_format($now, 'w')
 * for Sunday is 0 - for Monday the value is 1 - and so on.
 * Here, we need to compensate for this by checking to see if the value
 * of date_format($now, 'w') - 1 is -1, and, if so, bring Sunday
 * back into the valid range of values by setting $today to 6.
 */
if ($today == -1)
{
  $today = 6;
}

$response->redirect(AppletInstance::getDropZoneUrl(
  ($from = AppletInstance::getValue("range_{$today}_from"))
  && ($to = AppletInstance::getValue("range_{$today}_to"))
  && date_create($from) <= $now && $now < date_create($to)
  ? 'open'
  : 'closed'
));

$response->respond();
