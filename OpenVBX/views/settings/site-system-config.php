<form name="vbx-system" action="<?php echo site_url('settings/site') ?>" method="POST" class="vbx-system-form vbx-form">
	
	<fieldset>
	
		<h3>System Config</h3>
	
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
			<p class="instruction">(must be a CNAME for api.twilio.com)</p>
		</div>
	
	</fieldset>

	<fieldset>
	
		<h3>Transcriptions</h3>
	
		<fieldset class="vbx-input-complex vbx-input-container">
	
			<label class="field-label">Transcribe Recordings</label>
			<label for="transcribe-on" class="field-label-inline">Transcriptions ON
				<?php 
					$radio = array(
						'id' => 'transcribe-on',
						'name' => 'site[transcriptions]',
					);
					echo form_radio($radio, '1', ($transcriptions['value'] == 1)); 
				?>
			</label>
			<label for="transcribe-off" class="field-label-inline">Transcriptions OFF
				<?php
					$radio = array_merge(array(
							'id' => 'transcribe-off'
						), $radio);
					echo form_radio($radio, '0', ($transcriptions['value'] == 0));
				?>
			</label>						
	
		</fieldset>
	
	</fieldset>
	
	<fieldset>
	
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
						'woman' => 'Woman'
					);
					echo t_form_dropdown($params, $options, $voice['value']);
				?>
			</label>
		</div>

		<div class="vbx-input-complex vbx-input-container">
			<label class="field-label">Voice Language
				<?php
					$params = array(
						'name' => 'site[voice_language]',
						'id' => 'site-voice-lang',
						'class' => 'medium'
					);
					$options = array(
						'en' => 'English',
						'es' => 'Spanish',
						'fr' => 'French',
						'de' => 'German'
					);
					echo t_form_dropdown($params, $options, $voice_language['value']);
				?>
			</label>
		</div>
		
	</fieldset>
				
	<button class="submit-button" type="submit"><span>Update</span></button>

</form>