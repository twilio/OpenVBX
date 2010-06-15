<div class="vbx-applet">
	<?php if(AppletInstance::getFlowType() == 'voice'): ?>
	<h3>Send a text message to the caller if they're on the mobile phone.</h3>
	<?php else: ?>
	<h3>Send a text message to the sender.</h3>
	<?php endif; ?>
	<p>Note, not currently supported on Toll Free Numbers</p>
	<fieldset class="vbx-input-container">
		<textarea name="sms" class="medium"><?php echo AppletInstance::getValue('sms'); ?></textarea>
	</fieldset>
	
	<h2>Next</h2>
	<p>After the message is sent, continue to the next applet</p>
	<div class="vbx-full-pane">
		<?php echo AppletUI::DropZone('next'); ?>
	</div><!-- .vbx-full-pane -->

</div><!-- .vbx-applet -->
