<?php

class External extends MY_Controller {

	public function message_details($id) {
		return $this->request(site_url('messages/details/'.$id), array('iphone' => site_url('iphone/messages/details/'.$id)));
	}

	// Handle all external requests detecting if they're a mobile device otherwise pass-thru to target url
	protected function request($url, $alternativeURLs = array()) {
		$iphoneURL = $alternativeURLs['iphone'];
		$detection = <<<DETECTION
		<script type="text/javascript">
		function detection() {
			var agent = navigator.userAgent.toLowerCase();
			if((agent.indexOf('iphone') != -1)) {
				return document.location = '$iphoneURL';
			}

			document.location = '$url';
		}

		detection();
		</script>
DETECTION;

		echo $detection;
		exit;
	}
}