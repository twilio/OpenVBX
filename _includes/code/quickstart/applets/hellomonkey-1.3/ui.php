<?php 
$keys = AppletInstance::getValue('keys[]', array('1','2','3','4'));
$choices = AppletInstance::getValue('choices[]');
?>

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
			<td>Add &amp; Remove</td>
		</tr>
	</thead>
	<tbody>
	<?php foreach($keys as $i=>$key): ?>
		<tr>
			<td>
				<fieldset class="vbx-input-container">
					<input type="text" class="small" value="<?php echo $key ?>" name="keys[]"/>
				</fieldset>
			</td>
			<td>&nbsp;</td>
			<td>
				<?php echo AppletUI::dropZone('choices['.($i).']', 'Drop item here'); ?>
			</td>
			<td>
				<a href="" class="add action">
					<span class="replace">Add</span></a> <a href="" class="remove action"><span class="replace">Remove</span>
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr class="hide">
			<td>
				<fieldset class="vbx-input-container">
					<input type="text" class="small" value="" name="keys[]"/>
				</fieldset>
			</td>
			<td>&nbsp;</td>
			<td>
				<?php echo AppletUI::dropZone('new-choices[]', 'Drop item here'); ?>
			</td>
			<td>
				<a class="add action" href="">
					<span class="replace">Add</span>
				</a>
				<a class="remove action" href="">
					<span class="replace">Remove</span>
				</a>
			</td>
		</tr>
	</tfoot>
	</table>

	<h3>Fallback Action</h3>
	<p>This action will be executed if the caller is unknown.</p>
	<?php echo AppletUI::DropZone('fallback'); ?>
</div>


