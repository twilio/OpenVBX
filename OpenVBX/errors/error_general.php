<?php header("HTTP/1.1 404 Not Found"); ?>
<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>OpenVBX</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />

	<link type="text/css" rel="stylesheet" href="<?php echo ASSET_ROOT ?>/c/reset-fonts-grids-2.8.css" />
	<link type="text/css" rel="stylesheet" href="<?php echo ASSET_ROOT ?>/c/global.css" />

	<!--[if IE 7]>
		<link type="text/css" rel="stylesheet" href="<?php echo ASSET_ROOT ?>/c/ie.css" />
	<![endif]-->
</head>

<body>

<div id="doc3" class="yui-t6">

<div id="wrapper">

		<div id="hd">
		<h1 id="openvbx-logo"><a href="" class="navigate-away"><span class="replace">OpenVBX</span></a></h1>
		</div><!-- #hd -->

		<div id="bd" class="error-page">

		<div id="yui-main">
				<div class="yui-b">
					<div id="vbx-main">
						<div class="vbx-content-main">
							<p class="error-code">ERROR</p>
							<h1 class="error-title"><?php echo $heading; ?></h1>
							<p class="error-message"><?php echo $message; ?></p>
						</div><!-- .vbx-content-main -->
					</div><!-- #vbx-main -->
				</div><!-- .yui-b -->
		</div>

		<div class="yui-b">
			<div class="vbx-sidebar">
			</div><!-- .vbx-sidebar -->
		</div><!-- .yui-b -->

		</div><!-- #bd .error-404 -->

		<div id="ft">
		<p class="copyright">OpenVBX &bull; <em>v</em><?php echo OpenVBX::version() ?> r<?php echo OpenVBX::schemaVersion() ?> &mdash; Powered by <a href="http://twilio.com/">Twilio Inc.</a> &bull; <a href="http://www.twilio.com/legal/tos">Terms</a> &bull; <a href="http://www.twilio.com/legal/privacy">Privacy</a></p>
		</div><!-- #ft -->



</div><!-- #wrapper -->

</div><!-- #doc -->

</body>

</html>
