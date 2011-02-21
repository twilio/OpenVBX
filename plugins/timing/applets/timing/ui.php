<?php if (version_compare(PHP_VERSION, '5.2.0', '<=')): ?>
<div class="vbx-applet">
    <h1>This applet is not supported in php version <?= PHP_VERSION ?></h1>
</div>
<?php else:
$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
							'Saturday', 'Sunday');
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
$now = new DateTime('now', $tz);
?>
<div class="vbx-applet">
	<h2>Set your open hours.</h2>
	<p>Use the table below to set the hours which you are open. Time is based on
	the server's current time. We use the timezone specified in your
	<em>timing.ini</em> in the timing plugin directory.</p>
	<p><em>Your server's current time: <?php echo $now->format('r'); ?></em></p>

	<div class="vbx-full-pane">
<?php foreach ($days as $index => $day): ?>
		<div class="timing-timerange-wrap">
			<label><?php print $day; ?></label>
<?php
			$state = AppletInstance::getValue("range_{$index}_from}", '') ? 'remove' : 'add';
			$default = $index < 5 ? '09:00AM' : '';
			echo AppletUI::TimeRange(
				"range_$index",
				AppletInstance::getValue("range_{$index}_from", $default),
				AppletInstance::getValue("range_{$index}_to", '05:00PM'),
				$day
			);
?>
			<a href="#" class="timing-<?php echo $state; ?>"><?php echo $state; ?></a>
			<br class="clear"/>
		</div>
<?php endforeach; ?>
	</div>

	<h3>Open applet.</h3>
	<div class="vbx-full-pane">
		<p>When someone calls or SMS while open, use the applet below.</p>
		<?php echo AppletUI::DropZone('open', 'Open'); ?>
	</div>
	<h3>Closed applet.</h3>
	<div class="vbx-full-pane">
		<p>When someone calls or SMS while closed, use the applet below.</p>
		<?php echo AppletUI::DropZone('closed', 'Closed'); ?>
	</div>
</div>
<?php endif; ?>
