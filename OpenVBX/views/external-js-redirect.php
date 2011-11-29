<script type="text/javascript">
	function detection() {
		var agent = navigator.userAgent.toLowerCase();
		if((agent.indexOf('iphone') != -1)) {
			document.location = '<?php echo $iphoneURL; ?>';
		}
		else {
			document.location = '<?php echo $site_url; ?>';
		}
	}
	detection();
</script>