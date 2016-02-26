<?php

if(isset($_GET['json'])):

	$account = OpenVBX::getAccount();
	$conferences = $account->conferences->getIterator(0, 50, array('Status' => 'in-progress'));

	$res = array();
	
	foreach($conferences as $call) {
		$res[$call->friendly_name] = array(
			'date_created' => date("F j, Y, g:i a",strtotime($call->date_created)),
			'friendly_name' => $call->friendly_name,
			'status' => $call->status,
			'duration' => $call->duration
		);		
	}
	
	header('Content-type: application/json');
	echo json_encode($res);
	exit;
	
endif;

OpenVBX::addJS('pages/conferences.js');

?>
<div class="vbx-content-main">
	<div class="vbx-content-menu vbx-content-menu-top">
		<h2 class="vbx-content-heading">Conference Calls In Progress</h2>
	</div><!-- .vbx-content-menu -->
		<div class="vbx-content-container">
		<div class="vbx-content-section">
			<table class="vbx-items-grid" border="0">
				<thead>
					<tr class="items-head"><th>Start Time</th><th>Conference Name</th><th>Status</th><th>Join</th><th>Listen</th></tr>
				</thead>
				<tbody id="calls">
				</tbody>
			</table>
		</div><!-- .vbx-content-section -->
	</div><!-- .vbx-content-container -->
</div><!-- .vbx-content-main -->
