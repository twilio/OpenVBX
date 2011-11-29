<?php 
	global $dial_disabled;
	if (!empty($callerid_numbers) && count($callerid_numbers == 1)): /* callerid_number */ ?>

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

<?php elseif (!empty($callerid_numbers) && count($callerid_numbers > 1)): /* callerid_numbers */ ?>

	<?php $c = $callerid_numbers[0]; ?>
	<?php if(isset($c->trial) && $c->trial == 1): /* is-trail */ ?>
		<label class="field-label"><span class="label-text">From</span>
			<?php
				echo t_form_input(array(
						'name' => 'callerid',
						'class' => 'small'
					));
			?>
		</label>
	<?php else: /* is-trail */ ?>
		<?php echo form_hidden('callerid', $c->phone); ?>
	<?php endif; /* is-trail */ ?>

<?php else: /* callerid_numbers */ ?>

	<?php $dial_disabled = true; ?>
	<?php if(OpenVBX::getTwilioAccountType() == 'Trial'): /* trial-notice */ ?>
		<p>You&rsquo;re using a Twilio trial account, please upgrade to dial using a virtual phone number.</p>
	<?php else: /* trial-notice */ ?>
		<p>You do not have any Twilio Phone Numbers. Please purchase a phone number to enable this feature.</p>
	<?php endif; /* trial-notice */ ?>

<?php endif; /* callerid_numbers */ ?>