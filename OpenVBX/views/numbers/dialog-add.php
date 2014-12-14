<div id="dlg_add" title="Get a new number" class="dialog">
	<div class="hide error-message"></div>

	<form class="number-order-interface content ui-helper-clearfix vbx-form" action="<?php echo site_url('numbers/add'); ?>" method="post">
		<div class="number-order-options">
			<div id="country-select" class="vbx-input-container">
				<img src="<?php echo asset_url(''); ?>assets/i/countries/<?php echo strtolower($selected_country); ?>.png" />
				<?php
					$params = array(
						'name' => 'country',
						'id' => 'iCountry',
						'class' => 'small'
					);
					echo t_form_dropdown($params, $countries, $selected_country);
				?>
			</div>
			<div id="number-order-local" class="number-type-select">
				<input type="radio" id="iTypeLocal" name="type" value="local" checked="checked" />
				<label for="iTypeLocal" class="field-label-inline">Local</label>
			</div>
			<div id="number-order-toll_free" class="number-type-select">
				<input type="radio" id="iTypeTollFree" name="type" value="toll_free" />
				<label for="iTypeTollFree" class="field-label-inline">Toll-Free</label>
			</div>
		</div>
		
		<div id="pAreaCode" class="area-code">
			<fieldset class="vbx-input-complex vbx-input-container">
				<label for="iAreaCode" class="area-code-label">Area Code</label>
				<span id="number-input-wrapper"><span id="area-code-wrapper">1 + (<input type="text" id="iAreaCode" name="area_code" maxlength="5" />)</span> &hellip;</span>
			</fieldset>
		</div>
		<p>Buying a phone number will charge your Twilio account. See <a href="http://www.twilio.com/voice/pricing" target="_blank">Twilio.com</a> for pricing information.</p>
	</form>

	<div id="completed-order" class="hide">
		<p>Here's your new number</p>
		<p class="number"></p>
		<a href="" class="setup link-button">Setup Flow</a>
		<br class="clear" />
		<p><a href="<?php echo site_url('numbers') ?>" class="skip-link">Setup later</a></p>
	</div>
</div>