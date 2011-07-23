<script type="text/javascript">
	// global params
	OpenVBX = {home: null, assets: null, client_capability: null};
	OpenVBX.home = '<?php echo preg_replace("|/$|", "", site_url('')); ?>';
	OpenVBX.assets = '<?php echo preg_replace("|/$|", "", asset_url('')); ?>';
<?php if ($client_capability): ?>
	OpenVBX.client_capability = '<?php echo $client_capability; ?>';
<?php endif; ?>
</script>