<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<title><?php echo empty($title) ? ' ' : "$title | " ?><?php echo $site_title ?> <?php echo (isset($counts))? '('.$counts[0]->new.')' : '' ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
	<link rel="stylesheet" href="<?php echo asset_url('assets/c/iframe.css'); ?>" type="text/css" media="screen" />
	<script type="text/javascript" src="<?php echo asset_url('assets/j/frameworks/jquery-1.6.2.min.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('assets/j/frameworks/jquery-ui-1.8.14.custom.min.js'); ?>"></script>
	<script type="text/javascript" src="http://ajax.cdnjs.com/ajax/libs/underscore.js/1.1.4/underscore-min.js"></script>
	<script type="text/javascript" src="http://ajax.cdnjs.com/ajax/libs/backbone.js/0.3.3/backbone-min.js"></script>
	<script type="text/javascript" src="<?php echo $twilio_js; ?>"></script>
</head>
<body>
	<div id="container">
		<div id="dialer">
			<div class="client-ui-tab">
				<div class="client-ui-bg-overlay"><!-- leave me alone! --></div>
				<div class="client-ui-inset">
					<span class="wedge">&raquo;</span>
				</div>
			</div>
			<div class="client-ui-content">
				<div class="client-ui-bg-overlay"><!-- leave me alone! --></div>
				<div class="client-ui-inset">
					<div id="client-ui-status" class="clearfix">
						<h2 id="client-ui-message">Initializing...</h2>
						<h3 id="client-ui-timer">0:00</h3>
					</div>
					<div id="client-ui-pad" class="clearfix">
						<div class="client-ui-button-row">
							<div class="client-ui-button">
								<div class="client-ui-button-number">1</div>
								<div class="client-ui-button-letters"></div>
							</div>
							<div class="client-ui-button">
								<div class="client-ui-button-number">2</div>
								<div class="client-ui-button-letters">abc</div>
							</div>
							<div class="client-ui-button">
								<div class="client-ui-button-number">3</div>
								<div class="client-ui-button-letters">def</div>
							</div>
						</div>
						<div class="client-ui-button-row">
							<div class="client-ui-button">
								<div class="client-ui-button-number">4</div>
								<div class="client-ui-button-letters">ghi</div>
							</div>
							<div class="client-ui-button">
								<div class="client-ui-button-number">5</div>
								<div class="client-ui-button-letters">jkl</div>
							</div>
							<div class="client-ui-button">
								<div class="client-ui-button-number">6</div>
								<div class="client-ui-button-letters">mno</div>
							</div>
						</div>
						<div class="client-ui-button-row">
							<div class="client-ui-button">
								<div class="client-ui-button-number">7</div>
								<div class="client-ui-button-letters">pqrs</div>
							</div>
							<div class="client-ui-button">
								<div class="client-ui-button-number">8</div>
								<div class="client-ui-button-letters">tuv</div>
							</div>
							<div class="client-ui-button">
								<div class="client-ui-button-number">9</div>
								<div class="client-ui-button-letters">wxyz</div>
							</div>
						</div>
						<div class="client-ui-button-row">
							<div class="client-ui-button">
								<div class="client-ui-button-number asterisk">*</div>
								<div class="client-ui-button-letters"></div>
							</div>
							<div class="client-ui-button">
								<div class="client-ui-button-number">0</div>
								<div class="client-ui-button-letters"></div>
							</div>
							<div class="client-ui-button">
								<div class="client-ui-button-number">#</div>
								<div class="client-ui-button-letters"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div><!-- /dialer -->
		<iframe name="openvbx-iframe" id="openvbx-iframe" src="<?php echo $iframe_url; ?>" width="100%" height="100%" frameborder="no">
			<p>Your browser doesn't support iFrames.</p>
		</iframe>
	</div><!-- /container -->

<?php $this->load->view('js-init'); ?>
<script type="text/javascript" src="<?php echo asset_url('assets/j/iframe.js') ?>"></script>
</body>
</html>
