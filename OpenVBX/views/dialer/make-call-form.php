<?php
	// if the user has no incoming phone numbers or
	// is a trial account this will be flipped to true
	$dial_disabled = false;
?>
<div id="client-mode-status">
	<a id="client-mode-button" class="enabled" href="">Client</a>
	<a id="phone-mode-button" class="disabled" href="">Phone</a>
</div>

<form id="make-call-form" action="" method="POST">
	<fieldset>
		
		<label class="field-label"><span class="label-text">Call</span>
			<input id="dial-phone-number" type="text" placeholder="Phone number" />
		</label>
		
	<?php if (!empty($callerid_numbers) && count($callerid_numbers == 1)): /* callerid_number */ ?>
		
		<label class="field-label"><span class="label-text">From</span>
			<select id="caller-id-phone-number">
			<?php foreach ($callerid_numbers as $number): ?>
				<option value="<?php echo $number->phone ?>"><?php echo $number->phone ?></option>
			<?php endforeach; ?>
			</select>
		</label>
	
	<?php elseif (!empty($callerid_numbers) && count($callerid_numbers > 1)): /* callerid_numbers */ ?>
	
		<?php $c = $callerid_numbers[0]; ?>
		<?php if(isset($c->trial) && $c->trial == 1): /* is-trail */ ?>
			<label class="field-label"><span class="label-text">From</span>
				<input type="text" name="callerid" value="" class="small" />
			</label>
		<?php else: /* is-trail */ ?>
			<input type="hidden" name="callerid" value="<?php echo $c->phone ?>" />
		<?php endif; /* is-trail */ ?>
	
	<?php else: /* callerid_numbers */ ?>
	
		<?php $dial_disabled = true; ?>
		<?php if(OpenVBX::getTwilioAccountType() == 'Trial'): /* trial-notice */ ?>
			<p>You&rsquo;re using a Twilio trial account, please upgrade to dial using a virtual phone number.</p>
		<?php else: /* trial-notice */ ?>
			<p>You do not have any Twilio Phone Numbers. Please purchase a phone number to enable this feature.</p>
		<?php endif; /* trial-notice */ ?>
	
	<?php endif; /* callerid_numbers */ ?>
	
	</fieldset>
	<input id="dial-input-button" type="submit" value="Dial"<?php echo ($dial_disabled ? ' disabled="disabled"' : ''); ?> />	
</form><!-- #make-call-form -->

<?php if (count($users)): /* user-list */ ?>
						
	<ul id="client-ui-user-list">
		<?php foreach ($users as $user): ?>
			<li id="user-<?php echo $user->id; ?>" class="user-item no-icons"><!-- not handled, introduces too much ui clutter
				<span class="status-icon enabled-phone" title="Phone Enabled"></span>
				<span class="status-icon disabled-client" title="Client Disabled"></span>-->
				<span class="user-name"><?php echo $user->first_name.' '.$user->last_name; ?></span>
				<button class="user-dial-button"<?php echo ($dial_disabled ? ' disabled="disabled"' : ''); ?>>Dial</button>
				<input type="hidden" name="email" value="<?php echo $user->email; ?>" />
			</li>
		<?php endforeach; ?>
	</ul><!-- #client-ui-user-list -->
	
<?php endif; /* user-list */ ?>