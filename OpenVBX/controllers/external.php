<?php

class External extends MY_Controller {

	public function message_details($id) {
		return $this->request(site_url('messages/details/'.$id), array(
			'iphone' => site_url('iphone/messages/details/'.$id)
		));
	}

	/**
	 * Handle all external requests detecting if they're a mobile 
	 * device otherwise pass-thru to target url
	 */
	protected function request($url, $alternativeURLs = array()) {
		set_last_known_url($url);
		$iphoneURL = $alternativeURLs['iphone'];
		$site_url = site_url();

		$data = compact('iphoneURL', 'url', 'site_url');
		echo $this->load->view('external-js-redirect', $data, true);
		exit;
	}
}