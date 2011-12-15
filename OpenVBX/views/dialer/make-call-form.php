<?php
	// if the user has no incoming phone numbers or
	// is a trial account this will be flipped to true
	global $dial_disabled;
	$dial_disabled = false;
?>
<form id="make-call-form" action="" method="post">

	<div id="call-options">
		<div id="call-options-summary">
			<span id="summary-call-using" class="<?php echo $browserphone['call_using']; ?>"></span>
			<span id="summary-caller-id">Caller ID: <span><?php echo $browserphone['caller_id']; ?></span></span>
			<span id="summary-call-toggle">&raquo;</span>
		</div>
		
		<div id="call-options-inputs" style="display: none;">
			<div id="callerid-container">
				<?php $this->load->view('dialer/numbers'); ?>
			</div>
	
			<label class="field-label"><span class="label-text">Using</span>
				<?php $this->load->view('dialer/devices'); ?>
			</label>
			
			<div id="call-options-descriptions">
				<p id="call-option-description-browser">Your call will be placed using the browserphone.</p>
				<p id="call-option-description-device">You will be called at <span class="device-number">(000) 000-0000</span> and then connected to your destination.</p>
			</div>
			<p>Your Caller ID will be <span id="call-option-description-caller-id" class="device-number"><?php echo $browserphone['caller_id']; ?></span></p>
		</div>
	</div>

	<h2>Make a Call</h2>		
	
	<fieldset>
		<?php
			echo t_form_input(array(
					'name' => 'dial_phone_number',
					'id' => 'dial-phone-number',
					'placeholder' => '(555) 867 5309'
				), '');
			$call_button_params = array(
					'name' => 'dial_input_button',
					'id' => 'dial-input-button',
					'value' => '<span class="button-text">Call</span>'
				);
			if ($dial_disabled)
			{
				$call_button_params['disabled'] = 'disabled';
			}
			echo t_form_button($call_button_params);
		?>
	</fieldset>
</form><!-- #make-call-form -->

<?php if (count($users)): /* user-list */ ?>
	
	<h2 style="text-align: center; color: white; margin: 30px 0 5px 0;">Quick Dial</h2>
	<?php $this->load->view('dialer/users-list'); ?>
	
<?php endif; /* user-list */ ?>