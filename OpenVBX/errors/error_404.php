<?php header("HTTP/1.1 404 Not Found"); ?>
<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo empty($title) ? ' ' : "$title | " ?>OpenVBX</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
	<?php
	$theme = config_item('theme');
	$site_rev = config_item('site_rev');
	if(empty($theme)) $theme = 'default';

	$asset_root = ASSET_ROOT;
	$css = array(
				 "c/jplayer",
				 "c/bubble",
				 "c/twilio",
				 "themes/$theme/style",
				 );
	?>
	<link type="text/css" rel="stylesheet" href="<?php echo ASSET_ROOT ?>/c/required.css" />
	<?php foreach($css as $link): ?>
		<link type="text/css" rel="stylesheet" href="<?php echo ASSET_ROOT ?>/<?php echo $link ?>.css" />
	<?php endforeach; ?>
	<script type="text/javascript" src="<?php echo $asset_root ?>/j/compiled/<?php echo $site_rev ?>/site.js"></script>
	<?php

	$util_menu = '';
	$nav_menu = '';

	$logo_path = config_item('logo');
	if(empty($logo_path)) $logo_path = $asset_root . '/i/large_logo.png';

	?>
</head>
<div id="wrapper">
	<div id="head">
		<div class="content">
			<h1 id="logo"><?php printf('<a href="%s"><img src="%s" alt="logo" border="0" /></a>', '/', $logo_path); ?></h1>
			<?php echo $util_menu . $nav_menu; ?>
		</div>
	</div><!--END TOP-->
	<div id="content" class="404">
		<?php echo $message; ?>
	</div>
	<!-- <div id="footer"> -->
	<!-- <div class="content"> -->
				<!-- <a href="http://www.twilio.com"><img src="<?php echo $asset_root; ?>/i/twilio_logo.png" alt="Twilio: Telephone API for building IVR, PBX, Call Notifications, VoIP API applications, and more" border="0" /></a> -->
			<!-- </div> -->
		<!-- </div> -->
	</div>
</div><!--END WRAPPER -->
<div class="screen"></div>
</body>
</html>
