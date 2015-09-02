<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo empty($title) ? ' ' : "$title | " ?><?php echo $site_title ?> <?php echo (isset($counts))? '('.$counts[0]->new.')' : '' ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
	<?php echo $_styles; ?>
	<?php foreach($css as $link): ?>
	<link type="text/css" rel="stylesheet" href="<?php echo ASSET_ROOT ?>/<?php echo $link ?>.css" />
	<?php endforeach; ?>

	<!--[if IE 7]>
		<link type="text/css" rel="stylesheet" href="<?php echo ASSET_ROOT ?>/c/ie.css" />
	<![endif]-->
</head>
<body>

<!-- wrapper_header -->
<?php echo $wrapper_header; ?>

<!-- header -->
<?php echo $header; ?>

	<!-- utility_menu -->
	<?php echo $utility_menu; ?>

	<!-- context_menu -->
	<?php echo $context_menu; ?>



	<!-- content_header -->
	<?php echo $content_header; ?>

	<!-- content_main -->
	<?php echo $content_main; ?>

	<!-- content_sidebar -->
	<?php echo $content_sidebar; ?>

	<!-- content_footer -->
	<?php echo $content_footer; ?>



<!-- footer -->
<?php echo $footer; ?>

<!-- wrapper_footer -->
<?php echo $wrapper_footer; ?>
<?php echo $error_dialog; ?>
<?php echo $analytics; ?>
<?php $this->load->view('js-init'); ?>
<?php echo $_scripts; ?>
<script type="text/javascript">
//<![CDATA[
	if (window == window.top && !window.location.href.match('\/auth\/')) {
		$.cookie('last_known_url', window.location.href, 0, '/');
		window.location = OpenVBX.home;
	}
//]]>
</script>
</body>
</html>
