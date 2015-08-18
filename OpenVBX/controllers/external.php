<?php

class External extends MY_Controller {

	/**
	 * @param $id
	 */
	public function message_details($id) {
		$this->request(site_url('messages/details/'.$id), array(
			'iphone' => site_url('iphone/messages/details/'.$id)
		));
	}

	/**
	 * Handle all external requests detecting if they're a mobile 
	 * device otherwise pass-thru to target url
	 *
	 * @param $url
	 * @param array $alternativeURLs
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