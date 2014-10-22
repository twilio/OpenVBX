<div style="margin: 10px 0">
<?php if ($client_application_error === 1): ?>
	<p><b>Your Client Application Sid is empty.</b> The Browser Phone will not function correctly 
		while this value is empty. Please go to your <a href="http://www.twilio.com/user/account/apps" onclick="window.open(this.href); return false;">Twilio Account Portal</a>, 
		then copy the Application Sid (long string that begins with "AP") for your OpenVBX install, and paste it 
		in to this field.</p>
<?php elseif ($client_application_error === 2): ?>
	<p><b>Twilio Client Application not found.</b></p>
<?php elseif ($client_application_error === 3): ?>
	<p><b>One or more of the Twilio Client Application URLs are empty:</b></p>
<?php elseif ($client_application_error === 4): ?>
	<p><b>One or more of the Twilio Client Application URLs does not correctly match:</b></p>
<?php elseif ($client_application_error === 5): ?>
	<p><b>There was an error getting the Client Application data:</b> <?php echo $client_application_error_message; ?></p>
<?php endif; ?>

<?php if (in_array($client_application_error, array(2, 3, 4))): ?>
	<ul>
		<li><b>Voice Url:</b> <?php echo $client_application->voice_url; ?></li>
		<li><b>Voice Fallback Url:</b> <?php echo $client_application->voice_fallback_url; ?></li>
	</ul>
	
	<p><b>Click on the Update Button below to fix this error.</b></p>
<?php endif; ?>
</div>