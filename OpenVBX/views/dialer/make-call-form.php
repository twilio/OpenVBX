<?php
	// if the user has no incoming phone numbers or
	// is a trial account this will be flipped to true
	$dial_disabled = false;
?>
<form id="make-call-form" action="" method="POST">

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
					'id' => 'dial-phone-number',
					'placeholder' => 'Phone Number'
				));
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
	<ul id="client-ui-user-list" style="margin-top: 0;">
		<?php foreach ($users as $user): ?>
			<li id="user-<?php echo $user->id; ?>" class="user-item no-icons"><?php /* not handled, introduces too much ui clutter
				<span class="status-icon enabled-phone" title="Phone Enabled"></span>
				<span class="status-icon disabled-client" title="Client Disabled"></span> */ ?>
				<span class="user-name"><?php echo $user->first_name.' '.$user->last_name; ?></span>
				<button class="user-dial-button"<?php echo ($dial_disabled ? ' disabled="disabled"' : ''); ?>><span class="button-text">Call</span></button>
				<input type="hidden" name="email" value="<?php echo $user->email; ?>" />
			</li>
		<?php endforeach; ?>
	</ul><!-- #client-ui-user-list -->
	
<?php endif; /* user-list */ ?>