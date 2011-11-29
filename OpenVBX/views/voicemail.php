<div class="vbx-content-main">

	<div class="vbx-content-menu vbx-content-menu-top">
		<h2 class="vbx-content-heading">Voicemail</h2>
	</div><!-- .vbx-content-menu -->

	<div class="voicemail-blank <?php echo empty($voicemail_say) && empty($voicemail_play)? '' : 'hide' ?>">
		<h2>Hey, you haven&rsquo;t setup your voicemail!</h2>
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
							 $widget = new AudioSpeechPickerWidget(
											'voicemail', 
											$voicemail_mode, 
											$voicemail_say, 
											$voicemail_play, 
											'user_id:'.$this->session->userdata('user_id')
										);
							echo $widget->render();
						?>
					</div>
				</div><!-- .voicemail-container -->
			</div>
		</div><!-- .vbx-content-section -->
	</div><!-- .vbx-content-container -->
	
</div><!-- .vbx-content-main -->
