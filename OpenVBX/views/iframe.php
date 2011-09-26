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
	<script type="text/javascript" src="<?php echo asset_url('/assets/j/plugins/jquery.cookie.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('/assets/j/phono/jquery.phono.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('/assets/j/client.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo $twilio_js; ?>"></script>
	
</head>
<body>
	<div id="container">
		<div id="dialer" class="closed">

			<div class="client-ui-tab open">
				<div class="client-ui-bg-overlay"><!-- leave me alone! --></div>
				<div class="client-ui-inset">
					<div id="client-ui-tab-status">
						<div class="client-ui-tab-wedge"><a href="#dialer"><span class="symbol">&raquo;</span> Hide</a></div>
						<div class="client-ui-tab-status-inner">
							<div class="mic"></div>
							<h3 class="client-ui-timer">0:00</h3>
						</div><!-- .client-ui-tab-status-inner -->
					</div><!-- #client-ui-tab-status -->
				</div><!-- #client-ui-tab-inset -->
			</div><!-- .client-ui-tab .open -->

			<div class="client-ui-content">
				<div class="client-ui-bg-overlay"><!-- leave me alone! --></div>
				<div class="client-ui-inset">
					<div id="client-ui-status" class="clearfix">
						<h2 id="client-ui-message">Initializing...</h2>
						<h3 class="client-ui-timer">0:00</h3>
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
					</div><!-- /client-ui-pad -->
					<div id="client-ui-actions">
						<button id="client-ui-mute" class="client-ui-action-button mute">Mute</button>
						<button id="client-ui-answer" class="client-ui-action-button answer">Answer</button>
						<button id="client-ui-hangup" class="client-ui-action-button hangup">Hangup</button>
						<button id="client-ui-close" class="client-ui-action-button close">Close</button>
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
<?php $ci = &get_instance(); ?>
<input type="hidden" id="phono_api_key" value="<?php echo $ci->phono_api_key; ?>" />
</body>
</html>
