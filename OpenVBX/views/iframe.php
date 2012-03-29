<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<title><?php echo empty($title) ? ' ' : "$title | " ?><?php echo $site_title ?> <?php echo (isset($counts))? '('.$counts[0]->new.')' : '' ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
	<link rel="stylesheet" href="<?php echo asset_url('assets/c/iframe.css'); ?>" type="text/css" media="screen" />
<?php if ($this->config->item('use_unminimized_js')): ?>
	<script type="text/javascript" src="<?php echo asset_url('assets/j/frameworks/jquery-1.6.2.min.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('assets/j/frameworks/jquery-ui-1.8.14.custom.min.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('assets/j/plugins/jquery.cookie.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('assets/j/iframe.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('assets/j/client.js'); ?>"></script>
<?php else: ?>
	<script type="text/javascript" src="<?php echo asset_url('assets/min/?g=iframejs&v='.$site_rev); ?>"></script>
<?php endif; ?>	
</head>
<body>
	<div id="container">
		<?php $this->load->view('dialer/dialer'); ?>
		<iframe name="openvbx-iframe" id="openvbx-iframe" src="<?php echo $iframe_url; ?>" width="100%" height="100%" frameborder="no">
			<p>Your browser doesn't support iFrames.</p>
		</iframe>
	</div><!-- /container -->

<script type="text/javascript" src="<?php echo $twilio_js; ?>"></script>
<?php $this->load->view('js-init'); ?>
<script type="text/javascript" src="<?php echo asset_url('assets/j/iframe.js?v='.$site_rev) ?>"></script>
</body>
</html>
