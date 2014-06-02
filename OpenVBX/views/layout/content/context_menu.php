<div id="vbx-context-menu" class="context-menu">

	<div id="vbx-call-sms-buttons">
		<button class="call-button twilio-call" data-href="<?php echo site_url('messages/call') ?>"><span>Call</span></button>
		<button class="sms-button twilio-sms" data-href="<?php echo site_url('messages/sms') ?>"><span>SMS</span></button>
	</div>
	
	 <div id="vbx-client-status" class="<?php echo ($user_online == 1 ? 'online' : ''); ?>">
		<div class="client-button-wrap">
	     	<button class="client-button twilio-client">
	        	<span class="isoffline">Offline</span><span class="isonline">Online</span>
	     	</button>
		</div>
	 </div>

	<div class="sms-dialog">
		<a class="close action" href=""><span class="replace">close</span></a>
		<h3>Send a Text Message</h3>
		<form action="<?php echo site_url('messages/sms') ?>" method="post" class="sms-dialog-form vbx-form">
			<fieldset class="vbx-input-complex vbx-input-container">
				<label class="field-label left">To
					<input class="small" name="to" type="text" placeholder="(555) 867 5309" value="" />
				</label>
				<?php if(isset($callerid_numbers) && count($callerid_numbers) > 1): ?>
					<label class="field-label left">From
						<select name="from" class="small">
							<?php foreach($callerid_numbers as $number):
								if (!$number->capabilities->sms)
								{
									continue;
								} 
							?>
							<option value="<?php echo $number->phone ?>">
								<?php echo $number->name ?>
							</option>
							<?php endforeach; ?>
						</select>
					</label>
				<?php elseif(isset($callerid_numbers) && count($callerid_numbers) == 1): 
					$c = $callerid_numbers[0]; ?>
					<input type="hidden" name="from" value="<?php echo $c->phone ?>" />
				<?php endif; ?>
				
				<br class="clear" />

				<label class="field-label">Message
					<textarea class="sms-message" name="content" placeholder="Enter your message, must be 1600 characters or less. (higher rates may apply for messages of more than 160 characters)"></textarea><span class="count">1600</span>
				</label>
			</fieldset>

			<button class="send-sms-button sms-button"><span>Send SMS</span></button>
			<img class="sms-sending hide" src="<?php echo asset_url('assets/i/ajax-loader.gif'); ?>" alt="loading" />
		</form>
	</div> <!-- .sms-dialog -->

	<div class="notify <?php echo (isset($error) && !empty($error))? '' : 'hide' ?>">
	 	<p class="message">
			<?php if(isset($error) && $error): ?>
				<?php echo $error ?>
			<?php endif; ?>
			<a href="" class="close action"><span class="replace">Close</span></a>
		</p>
	</div><!-- .notify -->

</div><!-- #vbx-context-menu .context-menu -->

<?php if($user_online === 'client-first-run') { $this->load->view('banners/client-first-run'); } ?>
