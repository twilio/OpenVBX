<?php if (!empty($callerid_numbers) && count($callerid_numbers == 1)): /* callerid_number */ ?>

	<label class="field-label"><span class="label-text">From</span>
		<select id="caller-id-phone-number">
		<?php foreach ($callerid_numbers as $number): ?>
			<option value="<?php echo $number->phone ?>"><?php echo $number->name ?></option>
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