<div class="vbx-applet">

		<h2>Build your own TwiML</h2>
		<p><a href="http://www.twilio.com/docs/api/2010-04-01/twiml/" target="_blank">Learn more about TwiML</a></p>
		<fieldset class="vbx-input-container">
			<textarea name="twiml" class="large" placeholder="&lt;Say&gt;:)&lt;/Say&gt;"><?php echo AppletInstance::getValue('twiml') ?></textarea>
		</fieldset>


		<h2 class="settings-title">Next</h2>
		<p>After the message is sent, continue to the next applet</p>
		<div class="vbx-full-pane">
			<?php echo AppletUI::DropZone('next'); ?>
		</div>

</div><!-- .vbx-applet -->
