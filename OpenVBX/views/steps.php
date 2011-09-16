<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo $title; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>/assets/c/install.css" />
	<script type="text/javascript" src="<?php echo base_url() ?>/assets/j/frameworks/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url() ?>/assets/j/plugins/jquery.validate.js"></script>
	<script type="text/javascript" src="<?php echo base_url() ?>/assets/j/steps.js"></script>
	<?php $this->load->view('js-init'); ?>
	<script type="text/javascript" src="<?php echo base_url() ?>/assets/j/<?php echo $this->router->class; ?>.js"></script>
</head>
<body>
	<?php $this->load->view($this->router->class.'/main'); ?> 	
</body>
</html>