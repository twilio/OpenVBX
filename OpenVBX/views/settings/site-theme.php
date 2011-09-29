<h3>Theme</h3>

<form name="vbx-settings" action="<?php echo site_url('settings/site') ?>#theme" method="POST" class="vbx-settings-form vbx-form">

	<fieldset class="vbx-input-container">
		<label for="site-theme" class="field-label">Choose a theme
			<?php
				$params = array(
					'name' => 'site[theme]',
					'id' => 'site-theme',
					'class' => 'medium'
				);
				echo t_form_dropdown($params, $available_themes, $theme);
			?>
		</label>
	</fieldset>

	<button class="submit-button" type="submit"><span>Update</span></button>

</form>