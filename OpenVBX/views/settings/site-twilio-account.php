<form name="vbx-settings" action="<?php echo site_url('settings/site') ?>#twilio-account" method="POST" class="vbx-settings-form vbx-form" autocomplete="off">
	<div class="two-col">	
		<h3>Twilio Account</h3>

		<fieldset class="vbx-input-container">
		
			<label for="site-twilio-sid" class="field-label">
				Twilio SID
				<input id="site-twilio-sid" type="text" name="site[twilio_sid]" value="<?php echo @$twilio_sid['value'] ?>" class="medium" />
			</label>
		
			<label for="site-twilio-token" class="field-label">
				Twilio Token
				<input id="site-twilio-token" type="password" name="site[twilio_token]" value="<?php echo @$twilio_token['value'] ?>" class="medium" />
			</label>
		
			<label for="site-twilio-application-sid" class="field-label">
				Twilio Client Application SID
				<input id="site-twilio-application-sid" type="text" name="site[application_sid]" value="<?php echo @$application_sid['value']; ?>" class="medium" />
				<p class="instruction">This Sid identifies your install for the purposes of making<br />and receiving calls with <a href="http://www.twilio.com/api/client">Twilio Client</a>.</p>
			</label>
		
			<label for="site-from-email" class="field-label">
				From Email
				<input id="site-from-email" type="text" name="site[from_email]" value="<?php echo @$from_email['value'] ?>" class="medium" />
				<p class="instruction">This is the email address which which all outbound emails<br />from OpenVBX install will be addressed.</p>
			</label>
		
		</fieldset>

	<?php if($tenant_mode == Site::MODE_MULTI): ?>

		<h3>Twilio Connect Settings</h3>

		<fieldset class="vbx-input-container">
		
			<p>This Sid identifies your install for the purposes of using<br />Twilio Connect to authorize your Tenant accounts.</p>
		
			<br />
		
			<label for="site-twilio-connect-application-sid" class="field-label">
				Twilio Connect Application SID
				<input id="site-twilio-connect-application-sid" type="text" name="site[connect_application_sid]" value="<?php echo @$connect_application_sid['value']; ?>" class="medium" />
				<p class="instruction">Leave blank to not use Twilio Connect. Changing an existing<br />Sid will invalidate any existing Connect authorizations.</p>
				<p class="instruction">The Connect Application will be checked and updated for the<br />proper callback urls on save.</p>
			</label>
		
		</fieldset>

	<?php endif; ?>
	</div>
	
	<button class="submit-button" type="submit"><span>Update</span></button>
</form>