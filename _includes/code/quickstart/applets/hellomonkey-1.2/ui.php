<div class="vbx-applet monkey-applet">
	<h2>Create A Custom Message</h2>
	<p>Enter in a custom message that your callers will be greeted by.</p>
	<textarea class="medium" name="prompt-text"><?php 
		echo AppletInstance::getValue('prompt-text') 
	?></textarea>
	
	<br />
	
	<h2>Call Screening Options</h2>
	<table class="vbx-menu-grid options-table">
	<thead>
		<tr>
			<td>Number To Screen</td>
			<td>&nbsp;</td>
			<td>Action if caller matches</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<fieldset class="vbx-input-container">
					<input type="text" class="small" value="<?php 
						echo AppletInstance::getValue('key')  
					?>" name="key"/>
				</fieldset>
			</td>
			<td>&nbsp;</td>
			<td>
			<?php echo AppletUI::dropZone('primary', 'Drop item here'); ?>
			</td>
		</tr>
	</tbody>
	</table>

	<h3>Fallback Action</h3>
	<p>This action will be executed if the caller is unknown.</p>
	<?php echo AppletUI::DropZone('fallback'); ?>
</div>
