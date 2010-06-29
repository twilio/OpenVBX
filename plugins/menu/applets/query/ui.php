<?php
$defaultNumberOfChoices = 4;
$keys = AppletInstance::getValue('keys[]', array('1' => '', '2' => '', '3' => '', '4' => '') );
$responses = AppletInstance::getValue('responses[]');
?>

<div class="vbx-applet query-applet">

		<h2>Menu Prompt</h2>
		<fieldset class="vbx-input-container">
			<p>When the texter reaches this menu, they will read:</p>
			<textarea class="medium" name="prompt" placeholder="Tell the users your options"><?php echo AppletInstance::getValue('prompt') ?></textarea>
		</fieldset>

		<h2>Menu Options</h2>
		<table class="vbx-menu-grid options-table">
			<thead>
				<tr>
					<td>Keyword</td>
					<td>&nbsp;</td>
					<td>Reply</td>
					<td>Add &amp; Remove</td>
				</tr>
			</thead>
			<tfoot>
				<tr class="hide">
					<td>
						<fieldset class="vbx-input-container">
							<input class="keypress small" type="text" name="new-keys[]" value="" autocomplete="off" />
						</fieldset>
					</td>
					<td>then</td>
					<td>
						<fieldset class="vbx-input-container">
							<textarea class="response" name="new-responses[]"></textarea>
						</fieldset>
					</td>
					<td>
						<a href="" class="add action"><span class="replace">Add</span></a> <a href="" class="remove action"><span class="replace">Remove</span></a>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach($keys as $id => $key): ?>
				<tr>
					<td>
						<fieldset class="vbx-input-container">
							<input class="keypress small" type="text" name="keys[]" value="<?php echo $key ?>" autocomplete="off" />
						</fieldset>
					</td>
					<td>then</td>
					<td>
						<fieldset class="vbx-input-container">
							<textarea name="responses[]"><?php echo isset($responses[$id])? $responses[$id] : '' ?></textarea>
						</fieldset>
					</td>
					<td>
						<a href="" class="add action"><span class="replace">Add</span></a> <a href="" class="remove action"><span class="replace">Remove</span></a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table><!-- .vbx-menu-grid -->

</div><!-- .vbx-applet -->


	
