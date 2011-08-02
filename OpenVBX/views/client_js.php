jQuery(function($) {
	$(window).bind('unload', function() {
		Client.status.setWindowStatus(false);
	});
	
	client_params = <?php echo $client_params; ?>;
	if (client_params.to) {
		// outgoing
		Client.onready = function() {
			clientCall(client_params);
		}
	}
});