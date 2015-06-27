<form name="vbx-settings" action="<?php echo site_url('settings/site/account') ?>#twilio-account" method="post" class="vbx-settings-form vbx-form" autocomplete="off">
	<div class="two-col">	
		<h3>Twilio Account</h3>

		<fieldset class="vbx-input-container">
			<label for="site-twilio-sid" class="field-label<?php if (empty($twilio_sid['value'])) { echo ' info notice'; }; ?>">
				Twilio SID
				<?php
					if (empty($twilio_sid['value']))
					{
						$this->load->view('settings/twilio-sid-notices');
					}
					
					$params = array(
						'id' => 'site-twilio-sid',
						'name' => 'site[twilio_sid]',
						'type' => 'text',
						'class' => 'medium'
					);
					echo t_form_input($params, @$twilio_sid['value']);
				?>
			</label>
		
			<label for="site-twilio-token" class="field-label<?php if (empty($twilio_sid['value'])) { echo ' info notice'; }; ?>">
				Twilio Token
				<?php
					if (empty($twilio_token['value']) 
						&& $this->tenant->type != VBX_Settings::AUTH_TYPE_CONNECT)
					{
						$this->load->view('settings/twilio-token-notices');
					}
					
					$params = array(
						'id' => 'site-twilio-token',
						'name' => 'site[twilio_token]',
						'type' => 'text',
						'class' => 'medium'
					);
					
					if ($this->tenant->type == VBX_Settings::AUTH_TYPE_CONNECT)
					{
					    $params['disabled'] = 'disabled';
					    $twilio_token['value'] = 'n/a';
					}
					
					echo t_form_input($params, @$twilio_token['value']);
				?>
				<?php if ($this->tenant->type == VBX_Settings::AUTH_TYPE_CONNECT): ?>
					<p class="instruction">Your account is using Twilio Connect for authorization.<br />Your account SID is not required.</p>
				<?php endif; ?>
			</label>
		
			<label for="site-twilio-application-sid" class="field-label<?php if (!empty($client_application_error)) { echo ' info notice'; }; ?>">
				Twilio Client Application SID
				<?php 
					if (!empty($client_application_error)) {
						$this->load->view('settings/client-application-notices');
					}

					$params = array(
						'id' => 'site-twilio-application-sid',
						'name' => 'site[application_sid]',
						'type' => 'text',
						'class' => 'medium'
					);
                                        
					echo t_form_input($params, @$application_sid['value']);
				?>
				<p class="instruction">This Sid identifies your install for the purposes of making<br />and receiving calls with <a href="http://www.twilio.com/api/client">Twilio Client</a>. The Client Application<br />will be checked and updated for the proper callback urls on save.</p>
			</label>
		
			<label for="site-from-email" class="field-label">
				From Email
				<?php
					$params = array(
						'id' => 'site-from-email',
						'name' => 'site[from_email]',
						'type' => 'text',
						'class' => 'medium'
					);
					echo t_form_input($params, @$from_email['value']);
				?>
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
				<?php
					$params = array(
						'id' => 'site-twilio-connect-application-sid',
						'name' => 'site[connect_application_sid]',
						'type' => 'text',
						'class' => 'medium'
					);
					echo t_form_input($params, @$connect_application_sid['value']);
				?>
				<p class="instruction">Leave blank to not use Twilio Connect. Changing an existing<br />Sid will invalidate any existing Connect authorizations.</p>
				<p class="instruction">The Connect Application will be checked and updated for the<br />proper callback urls on save.</p>
			</label>
		</fieldset>

	<?php endif; ?>
	</div>
	
	<button class="submit-button" type="submit"><span>Update</span></button>
</form>