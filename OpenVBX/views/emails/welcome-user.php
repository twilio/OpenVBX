Welcome to your new account<?php echo (!empty($name)) ? ', '.$name : ''; ?>.


To get setup accessing voicemail and SMS messages, you need to set a password for your account.
	Click on the following link to complete the process:
<?php echo $reset_url ?>
<?php if ($connect_notice): /* @todo - need good way to trigger this */ ?>
When logging in for the first time you'll be prompted to connect your Twilio Account to your VBX Account. If you do not currently have a Twilio Account you'll have the opportunity to sign up for one at that time.
<?php endif; ?>