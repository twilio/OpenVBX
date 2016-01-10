<?php

/**
 * Class iPhone
 * @property VBX_Message $vbx_message
 */
class iPhone extends MY_Controller {

	public function install() {
		$server = site_url();
		$email = $this->input->get_post('email', '');
		$https = preg_match('#^https://#', $server);
		$query = http_build_query(compact('https', 'server', 'email'));
		$this->redirect('setup?'.$query);
	}

	public function message_details($id) {
		// Building this URI schema
		// messages?folderId=0&messageId=30&folderName=Inbox&recordingURL=
		$this->load->model('vbx_message');
		$message = $this->vbx_message->get_message($id);
		if(!$message)
			show_404($id);

		$folderId = ($message->owner_type != 'group')? 0 : $message->owner_id;
		$messageId = $message->id;
		$folderName = 'Inbox';
		$recordingURL = $message->content_url.'.mp3';

		$query = http_build_query(compact('folderId', 'messageId', 'folderName', 'recordingURL'));
		$this->redirect('messages?'.$query);
	}

	protected function redirect($path) {
		$storeUrl = 'http://itunes.apple.com/app/id403429069';
		$appUrl = iphone_handler_url($path);
		$detectMethod = <<<TIMEOUT
		<script type="text/javascript">
		setTimeout(function() {
		   window.location = '$storeUrl';
	    }, 25);

		window.location = '$appUrl';
		</script>
TIMEOUT;

		echo $detectMethod;

		exit;
	}

}