		<div id="vbx-context-menu" class="context-menu">

			<div id="vbx-call-sms-buttons">
				<button class="call-button twilio-call" href="<?php echo site_url('messages/call') ?>"><span>Call</span></button>
				<button class="sms-button twilio-sms" href="<?php echo site_url('messages/sms') ?>"><span>SMS</span></button>
			</div>
			
			 <div id="vbx-client-status" class="<?php echo ($user_online == 1 ? 'online' : ''); ?>">
				<div class="client-button-wrap">
			     	<button class="client-button twilio-client" href="#client-status">
			        	<span class="isoffline">Offline</span><span class="isonline">Online</span>
			     	</button>
				</div>
			 </div>

			<div class="call-dialog">
				<a href="" class="close action"><span class="replace">close</span></a>
				<?php if(!empty($user_numbers) && count($user_numbers) < 1):  /* has-numbers */ ?>

				<p class="instruct">To use the call feature, <a href="<?php echo site_url('account#devices') ?>">register a phone number</a>.</p>

				<?php else: /* has-numbers */ ?>

				<h3>Make a call</h3>
				<form action="<?php echo site_url('messages/call') ?>" method="POST" class="call-dialog-form vbx-form">
					<fieldset class="vbx-input-complex vbx-input-container">
						<label class="field-label left">Dial
							<input id="dial-number" class="small" name="to" type="text" <?php echo empty($callerid_numbers)? 'disabled="disabled"' : '' ?>/>
						</label>

						<?php if(!empty($callerid_numbers) && count($callerid_numbers) > 1): /* num-numbers */?>
							<label class="field-label left">From
								<select name="callerid" class="small">
									<?php foreach($callerid_numbers as $number): ?>
									<option value="<?php echo $number->phone ?>"  rel="<?php echo @$number->api_type; ?>" app="<?php echo @$number->app_address; ?>"><?php echo $number->phone ?></option>
									<?php endforeach; ?>
								</select>
							</label>
					    <?php elseif(!empty($callerid_numbers) && count($callerid_numbers) == 1): /* num-numbers */?>
							<?php $c = $callerid_numbers[0]; ?>
							<?php if(isset($c->trial) && $c->trial == 1): /* is-trail */?>
							<label class="field-label left">From
								<input type="text" name="callerid" value="" class="small" />
							</label>
							<?php else: /* is-trail */ ?>
							<input type="hidden" name="callerid" value="<?php echo $c->phone ?>" />
							<?php endif; /* is-trail */ ?>
						<?php else: /* num-numbers */ ?>
							<?php if(OpenVBX::getTwilioAccountType() == 'Trial'): /* trial-notice */ ?>
							<p>You're using a Twilio trial account, please upgrade to dial using a virtual phone number.</p>
							<?php else: ?>
							<p>We were unable to connect to Twilio at this time. This feature is disabled.  Try again later.</p>
							<?php endif; /* trial-notice */ ?>
						<?php endif; /* num-numbers */ ?>


							<label class="field-label left">Using
								<select name="device" class="small">
									<option value="client">Web Client</option>
									<option value="primary-device">Primary Device</option>
								</select>
							</label>

					</fieldset>

					<input name="target" type="hidden" />

					<button class="call-button invoke-call-button" <?php echo empty($callerid_numbers)? 'disabled="disabled"' : '' ?>><span>Call</span></button>  <img class="call-dialing hide" src="<?php echo asset_url('assets/i/ajax-loader.gif'); ?>" alt="loading" />

				</form>

			</div><!-- .call-dialog  -->
			<div class="sms-dialog">
				<a class="close action" href=""><span class="replace">close</span></a>
				<h3>Send a Text Message</h3>
				<form action="<?php echo site_url('messages/sms') ?>" method="POST" class="sms-dialog-form vbx-form">
					<fieldset class="vbx-input-complex vbx-input-container">
						<label class="field-label left">To
							<input class="small" name="to" type="text" placeholder="(555) 867 5309" value="" />
						</label>
						<?php if(isset($callerid_numbers) && count($callerid_numbers) > 1): ?>
						<label class="field-label left">From
							<select name="from" class="small">
								<?php foreach($callerid_numbers as $number): ?>
								<option value="<?php echo $number->phone ?>"><?php echo $number->phone ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<?php elseif(isset($callerid_numbers) && count($callerid_numbers) == 1): $c = $callerid_numbers[0]; ?>
						<input type="hidden" name="from" value="<?php echo $c->phone ?>" />
						<?php endif; ?>
						<br class="clear" />

						<label class="field-label">Message
							<textarea class="sms-message" name="content" placeholder="Enter your message, must be 160 characters or less."></textarea><span class="count">160</span>
						</label>
					</fieldset>

					<button class="send-sms-button sms-button"><span>Send SMS</span></button>
					<img class="sms-sending hide" src="<?php echo asset_url('assets/i/ajax-loader.gif'); ?>" alt="loading" />
				</form>
			</div> <!-- .sms-dialog -->
			<?php endif; /* has-numbers */ ?>

			<div class="notify <?php echo (isset($error) && !empty($error))? '' : 'hide' ?>">

			 	<p class="message">
					<?php if(isset($error) && $error): ?>
						<?php echo $error ?>
					<?php endif; ?>
					<a href="" class="close action"><span class="replace">Close</span></a>
				</p>

			</div><!-- .notify -->

		</div><!-- #vbx-context-menu .context-menu -->
		
		<?php if($user_online === 'client-first-run') { $this->load->view('client-first-run'); } ?>
