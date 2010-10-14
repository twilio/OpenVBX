<div class="vbx-applet">
	<?php if(AppletInstance::getFlowType() == 'voice'): ?>
	<h2 class="start-heading">When a call begins, what should we do?</h2>
	<h3 class="start-instruct">Drag an applet from the right to get started.</h3>
	<?php else: ?>
	<h2 class="start-heading">When an SMS message is received, <br />what should we do?</h2>
	<h3 class="start-instruct">Drag an applet from the right to get started.</h3>
	<?php endif; ?>
	<?php echo AppletUI::dropZone('next'); ?>
</div><!-- .vbx-applet -->
	
