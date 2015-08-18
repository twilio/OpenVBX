<?php 
	global $dial_disabled;
	if (!empty($callerid_numbers) && count($callerid_numbers) > 0): /* callerid_numbers */ ?>

	<label class="field-label"><span class="label-text">Caller ID</span>
		<?php
			$params = array(
				'name' => 'browserphone_caller_id',
				'id' => 'caller-id-phone-number'
			);
			echo t_form_dropdown($params, 
								$browserphone['number_options'], 
								$browserphone['caller_id']
							);
		?>
	</label>

<?php else: /* callerid_numbers */ ?>

	<?php $dial_disabled = true; ?>
	<?php if(OpenVBX::getTwilioAccountType() == 'Trial'): /* trial-notice */ ?>
		<p>You&rsquo;re using a Twilio trial account, please upgrade to dial using a virtual phone number.</p>
	<?php else: /* trial-notice */ ?>
		<p>You do not have any Twilio Phone Numbers. Please purchase a phone number to enable this feature.</p>
	<?php endif; /* trial-notice */ ?>

<?php endif; /* callerid_numbers */ ?>