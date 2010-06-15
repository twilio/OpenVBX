<div class="vbx-content-main">

	<div class="vbx-content-menu vbx-content-menu-top">
		<h2 class="vbx-content-heading">Voicemail</h2>
	</div><!-- .vbx-content-menu -->

	<div class="voicemail-blank <?php echo empty($voicemail_say) && empty($voicemail_play)? '' : 'hide' ?>">
		<h2>Hey, you haven't setup your voicemail!</h2>
		<p>Change your greeting to: read text, play an audio file, or record it from your phone.</p>
	</div>	

	<div class="vbx-content-container">

		<div class="vbx-content-section">
			<div class="vbx-form">

				<h3>Voicemail</h3>
				<div class="voicemail-container">
					<div class="voicemail-icon standard-icon"><span class="replace">Voicemail</span></div>
					<div class="voicemail-label">Greeting</div>
					<div class="voicemail-picker">
						<?php
							 $widget = new AudioSpeechPickerWidget('voicemail', $voicemail_mode, $voicemail_say, $voicemail_play, 'user_id:' . $this->session->userdata('user_id'));
						echo $widget->render();
						?>
					</div>
				</div><!-- .voicemail-container -->


			</div>

		</div><!-- .vbx-content-section -->

	</div><!-- .vbx-content-container -->

</div><!-- .vbx-content-main -->



<div id="dialog-number" style="display: none;" class="new number dialog" title="Add Devices">
	<div class="hide error-message"></div>
	<div class="vbx-form">
		<fieldset class="vbx-input-container">
			<label class="field-label">Device Name
				<input type="text" class="medium" name="number[name]" value="" />
			</label>
		</fieldset>
		
		<fieldset class="vbx-input-container">
			<label class="field-label">Phone Number
				<input type="text" class="medium" name="number[value]" value="" />
			</label>
		</fieldset>
	</div>
</div>

