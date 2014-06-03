<div class="vbx-applet monkey-applet">
	<h2>Create A Custom Message</h2>
	<p>Entere in a custom message that your callers will be greeted by.</p>
	<textarea class="medium" name="prompt-text"><?php 
		echo AppletInstance::getValue('prompt-text') 
	?></textarea>

	<br />
	<h2>Select An Action For The Caller</h2>
	<?php echo AppletUI::DropZone('primary'); ?>
</div>

