<?php
// a little trickery to get a page load without loading CI
// not too worried, this is 0.0001% edge case coverage to avoid support requests
define('BASEPATH', NULL);
include('OpenVBX/config/config.php');
include('OpenVBX/config/version.php');
include('OpenVBX/config/constants.php');
?>
<html>
<head>
	<title>OpenVBX <?php echo $config['version']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
	<link type="text/css" rel="stylesheet" href="<?php echo $config['base_url']; ?>assets/c/reset-fonts-grids-2.8.css" />
	<link type="text/css" rel="stylesheet" href="<?php echo $config['base_url']; ?>assets/c/global.css" />
	<!--[if IE 7]>
		<link type="text/css" rel="stylesheet" href="<?php echo $config['base_url']; ?>assets/c/ie.css" />
	<![endif]-->
</head>
<body>
	<div id="doc3" class="yui-t6">
		<div id="wrapper">
			<div id="hd">
				<h1 id="openvbx-logo"><a href="" class="navigate-away">
					<span class="replace">OpenVBX</span></a>
				</h1>
			</div><!-- #hd -->
				
			<div id="bd" class="error-page">
				<div id="yui-main">
						<div class="yui-b">
							<div id="vbx-main">
								<div class="vbx-content-main">
									<p class="error-code">ERROR</p>
									<h1 class="error-title">Cannot install OpenVBX <?php echo $config['version']; ?></h1>
									<p class="error-message">OpenVBX requires PHP <?php echo MIN_PHP_VERSION; ?>.<br />
					This server is running <?php echo PHP_VERSION; ?>.</p>
									<p class="error-message">Please upgrade PHP or contact your hosting provider about upgrading your account. Installation requirements can be found in the <a href="http://openvbx.org/install/">OpenVBX Install Guide</a>.</p>
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
			<p class="copyright">OpenVBX &bull; <em>v</em><?php echo $config['version']; ?> &mdash; Powered by <a href="http://twilio.com/">Twilio Inc.</a> &bull; <a href="http://www.twilio.com/legal/tos">Terms</a> &bull; <a href="http://www.twilio.com/legal/privacy">Privacy</a></p>
			</div><!-- #ft -->
		</div><!-- #wrapper -->
	</div><!-- #doc3 -->
</body>
</html>