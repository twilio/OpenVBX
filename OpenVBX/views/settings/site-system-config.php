<form name="vbx-system" action="<?php echo site_url('settings/site') ?>#system-config" method="post" class="vbx-system-form vbx-form">
	<div class="two-col">

		<fieldset>	
			<h3>System Config</h3>

		<?php if($tenant_mode == Site::MODE_MULTI): /* PARENT TENANT ONLY */ ?>	
			<div class="vbx-input-complex vbx-input-container">
				<label for="rewrite" class="field-label">Do you want to enable mod_rewrite support?
					<?php
						$params = array(
							'name' => 'site[rewrite_enabled]',
							'id' => 'rewrite',
							'class' => 'medium'
						);
						$options = array(
							0 => 'No',
							1 => 'Yes'
						);
						echo t_form_dropdown($params, $options, $rewrite_enabled['value']);
					?>
				</label>
			</div>

			<div class="vbx-input-complex vbx-input-container">
				<label for="override" class="field-label">Hostname to use in recording URLs
					<input class="medium" id="override" name="site[recording_host]" value="<?php echo @$recording_host["value"]; ?>">
				</label>
				<p class="instruction">Must be a CNAME for api.twilio.com<br />See the Twilio documentation on <a href="http://www.twilio.com/docs/api/rest/tips#vanity-urls">Vanity Urls</a> for more info.</p>
				<br />
			</div>
		<?php endif; /* END PARENT TENANT ONLY */ ?>
				
			<div class="vbx-input-complex vbx-input-container">
				<label for="time_zone" class="field-label">Time Zone
				<?php
					$params = array(
						'name' => 'site[server_time_zone]',
						'id' => 'time_zone',
						'class' => 'medium'
					);
					echo t_form_dropdown($params, $time_zones, $server_time_zone['value']);
				?>
				</label>
				<br />
			</div>
			
			<div id="settings-email-notifications" class="vbx-input-complex vbx-input-container">
				<label class="field-label">Email Notifications</label>
				<label for="settings-email-notifications-voice" class="field-label-inline">
				<?php
					$params = array(
						'name' => 'site[email_notifications_voice]',
						'id' => 'settings-email-notifications-voice'
					);
					echo form_checkbox($params, '1', ($email_notifications_voice['value'] == 1));
				?> New Voicemail
				</label>
				<label for="settings-email-notifications-sms" class="field-label-inline">
				<?php
					$params = array(
						'name' => 'site[email_notifications_sms]',
						'id' => 'settings-email-notifications-sms'
					);
					echo form_checkbox($params, '1', ($email_notifications_sms['value'] == 1));
				?> New SMS
				</label>
				<p class="instruction">Control whether new Voice or SMS messages trigger an email<br />notification to the recipient(s).</p>
				<br />
			</div>
		</fieldset>
	
	<?php if (count($countries)): ?>
		<fieldset>
			<h3>International</h3>
		
			<div id="settings-country-select" class="vbx-input-complex vbx-input-container">
				<label for="country" class="field-label">Default Country for Phone Number Purchasing</label>
				<?php
					$params = array(
						'name' => 'site[numbers_country]',
						'id' => 'country',
						'class' => 'small'
					);
					echo t_form_dropdown($params, $countries, $numbers_country['value']);
				?>
				<img src="<?php echo asset_url('assets/i/countries/'.strtolower($numbers_country['value']).'.png'); ?>" alt="" />
			</div>
		</fieldset>
	<?php endif; /* count $countries */?>

		<fieldset class="vbx-input-container">
	
			<h3>Transcriptions</h3>
	
			<fieldset class="vbx-input-complex vbx-input-container">
	
				<label class="field-label">Transcribe Recordings</label>
				<label for="transcribe-on" class="field-label-inline">
					<?php 
						$radio = array(
							'id' => 'transcribe-on',
							'name' => 'site[transcriptions]',
						);
						echo form_radio($radio, '1', ($transcriptions['value'] == 1)); 
					?> Transcriptions ON
				</label>
				<label for="transcribe-off" class="field-label-inline">
					<?php
						$radio = array_merge(array(
								'id' => 'transcribe-off'
							), $radio);
						echo form_radio($radio, '0', ($transcriptions['value'] == 0));
					?> Transcriptions OFF
				</label>
				
			</fieldset>						
			<p class="instruction">See the Twilio Documentation on <a href="http://www.twilio.com/docs/api/rest/transcription">Transcriptions</a> for more info.</p>
	
		</fieldset>
	
		<fieldset class="vbx-input-container">
			
			<h3>Dialing</h3>
			
			<div class="vbx-input-complex vbx-input-container">
				<label class="field-label">Dial Timeout
					<?php
						$params = array(
							'name' => 'site[dial_timeout]',
							'id' => 'site-dial-timeout',
							'class' => 'medium'
						);
						$options = array();
						for ($i = 1; $i <= 60; $options[$i] = $i, $i += 1);
						echo t_form_dropdown($params, $options, $dial_timeout['value']);
					?>
				</label>
			</div>
			
			<p class="instruction">Sets the amount of time a Dial will wait until it gives up. Affects<br />the Dial applet and the browser phone when making outgoing calls.</p>
		</fieldset>
	
		<fieldset class="vbx-input-container">
	
			<h3>Text to Speech</h3>
		
			<div class="vbx-input-complex vbx-input-container">
				<label class="field-label">Voice
					<?php
						$params = array(
							'name' => 'site[voice]',
							'id' => 'site-voice',
							'class' => 'medium'
						);
						$options = array(
							'man' => 'Man',
							'woman' => 'Woman',
							'alice' => 'Alice',
						);
						echo t_form_dropdown($params, $options, $voice['value']);
					?>
				</label>
			</div>

			<div class="vbx-input-complex vbx-input-container<?php
				echo $voice['value'] !== 'man' && $voice['value'] !== 'woman' ? ' hide' : '';
			?>" id="lang-code-default">
				<label class="field-label">Voice Language
					<?php
						echo t_form_dropdown(array(
							'name' => 'site[voice_language]',
							'id' => 'site-voice-lang-default',
							'class' => 'medium'
						), $lang_codes['default'], $voice_language['value']);
					?>
				</label>
			</div>

			<div class="vbx-input-complex vbx-input-container<?php
				echo $voice['value'] == 'man' || $voice['value'] == 'woman' ? ' hide' : '';
			?>" id="lang-code-extended">
				<label class="field-label">Voice Language
					<?php
						echo t_form_dropdown(array(
							'name' => 'site[voice_language]',
							'id' => 'site-voice-lang-extended',
							'class' => 'medium'
						), $lang_codes['extended'], $voice_language['value']);
					?>
				</label>
			</div>

			<p class="instruction">See the Twilio Documentation for <a href="http://www.twilio.com/docs/api/twiml/say#attributes-voice">Voice &amp; Language Attributes</a><br />for more info.</p>
		</fieldset>
	</div>
				
	<button class="submit-button" type="submit"><span>Update</span></button>
</form>
