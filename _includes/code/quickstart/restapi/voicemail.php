<html>
<head>
	<title>My Messages REST API Sample</title>
	<style type="text/css">
		table {
			width:auto;
			border:1px solid #fff;
		}
		th {
			background:#D0D0D0 none repeat scroll 0 0;
			font-weight:bold;
			padding:5px;
			border:1px solid #fff;
		}
		td {
			background:#F0F0F0 none repeat scroll 0 0;
			padding:5px;
			border:1px solid #fff;
		}
	</style>
</head>
<body>

<?php

// @start snippet

//Define the URL to your voicemail and add the ID to the voicemail folder you want. Making the id 0 will retrieve all messages
$url = 'http(s)://[install-root]/messages/inbox/[id]';

// Initialize the session
$session = curl_init($url);

//Change this to your username and password used for your OpenVBX install
$username = 'username';
$password = 'password';

// Set curl options
$headers = array(
	 'Accept: application/json',
);

curl_setopt($session, CURLOPT_URL, $url);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
curl_setopt($session, CURLOPT_FAILONERROR, true);
curl_setopt($session, CURLOPT_USERPWD, $username.':'.$password);
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
{
	   curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
}

// Make the request
$response = curl_exec($session);

// Close the curl session
curl_close($session);

//Using the json decode function will turn the json into php objects
$response = json_decode($response);
// @end snippet

?>
	<!-- // @start snippet -->
	<table style="padding:10px;">
		<thead>
			<tr>
				<th>Number</th>
				<th>Duration</th>
				<th width="300px">Summary</th>
				<th>Status</th>
				<th>Message Type</th>
				<th>Date</th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach($response->messages->items AS $vm){
					echo "<tr>";
						echo "<td>".$vm->caller."</td>";
						echo "<td>".$vm->recording_length."</td>";
						echo "<td><a href='http(s)://[install-root]/messages/details/".$vm->id."/'>".$vm->short_summary."</a></td>";
						echo "<td>".$vm->ticket_status."</td>";
						echo "<td>".$vm->type."</td>";
						echo "<td>".date("M j", strtotime($vm->received_time))."</td>";
					echo "</tr>";
				}
			?>	
		</tbody>
	</table>
	<!-- // @end snippet -->
</body>
</html>
