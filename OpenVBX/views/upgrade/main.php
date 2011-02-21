<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Upgrade OpenVBX</title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>/assets/c/install.css" />
	<script type="text/javascript" src="<?php echo base_url() ?>/assets/j/frameworks/jquery-1.4.2.js"></script>
	<script type="text/javascript" src="<?php echo base_url() ?>/assets/j/plugins/jquery.validate.js"></script>
	<script type="text/javascript" src="<?php echo base_url() ?>/assets/j/upgrade.js"></script>

</head>
<body>

	<div id="install-container">

	<h1 id="openvbx-logo"><a href="<?php echo site_url() ?>/"><span class="replace">OpenVBX</span></a></h1>

	<form id="install-form" method="post" action="">

	<p class="error ui-widget-overlay"><?php if(isset($error)) echo $error; ?></p>

	<div class="steps">

		<div class="step">
			<a target="_blank" class="help" href="http://openvbx.org/install#upgrade" title="Get help at OpenVBX.org">Help</a>
			<h1>Upgrade Database</h1>
			<div class="step-desc">
				<p>Hey, it looks like your OpenVBX installation needs to be upgraded before you continue.</p>
	            <div class="upgrade-warning">
					<p>If you are using 3rd party plugins, make sure
						they support the 2010-04-01 Twilio API.</p>
					<p>Installed Plugins:</p>
            		<ul class="plugin-list">
						<?php foreach($plugins as $plugin): ?>
						<li><?php echo $plugin['name']; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div><!-- .step-desc -->

		</div><!-- .step -->

		<div class="step">
			<a target="_blank" class="help" href="http://openvbx.org/install#upgrade" title="Get help at OpenVBX.org">Help</a>
			<h1>Database Upgraded</h1>
			<div class="step-desc">
				<p>Your database is now ready!</p>
			</div><!-- .step-desc -->

			<a id="goto-openvbx" href="<?php echo site_url() ?>">Continue to Inbox</a>

		</div><!-- .step -->


	</div><!-- .steps -->

	<div class="navigation">
		<button class="next">Continue &raquo;</button>
		<button class="submit">Upgrade &raquo;</button>
	</div>


	</form>

	</div><!-- #install-container -->

	<script type="text/javascript">
		$(document).ready(function() {
			OpenVBX.home = '<?php echo site_url(''); ?>/';
			OpenVBX.assets = '<?php echo asset_url(''); ?>';
		<?php if(isset($step)): ?>
			OpenVBX.Installer.gotoStep(<?php echo $step ?>);
		<?php endif; ?>
		});
	</script>


</body>
</html>
