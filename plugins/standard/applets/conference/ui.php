<?php
$defaultWaitUrl = 'http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical';
$waitUrl = AppletInstance::getValue('wait-url', $defaultWaitUrl);
$musicOptions = array(
					  array("url" => "http://twimlets.com/holdmusic?Bucket=com.twilio.music.classical",
							"name" => "Classical"),
					  array("url" => "http://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient",
							"name" => "Ambient"),
					  array("url" => "http://twimlets.com/holdmusic?Bucket=com.twilio.music.electronica",
							"name" => "Electronica"),
					  array("url" => "http://twimlets.com/holdmusic?Bucket=com.twilio.music.guitars",
							"name" => "Guitars"),
					  array("url" => "http://twimlets.com/holdmusic?Bucket=com.twilio.music.rock",
							"name" => "Rock"),
					  array("url" => "http://twimlets.com/holdmusic?Bucket=com.twilio.music.soft-rock",
							"name" => "Soft Rock"),
					  );
$record = AppletInstance::getValue('record','do-not-record');
?>
<div class="vbx-applet">
		<h2>Moderator</h2>
		<p>If you set a moderator, callers are placed on hold until a moderator calls in from one of their configured devices.</p>
		<?php echo AppletUI::UserGroupPicker('moderator'); ?>

		<h2>Hold Music</h2>
		<p>Music is played until two or more people have dialed in, or until a moderator has joined.</p>
		<div class="vbx-full-pane">
		<fieldset class="vbx-input-container">
			<select name="wait-url" class="medium">
				<?php foreach($musicOptions as $option): ?>
				<option value="<?php echo $option['url']?>" <?php echo ($waitUrl == $option['url'])? 'selected="selected"' : '' ?>><?php echo $option['name']; ?></option>
				<?php endforeach; ?>
			</select>
			<input type="hidden" name="conf-id" value="<?php echo AppletInstance::getValue('conf-id', 'conf_'.mt_rand()) ?>" />
		</fieldset>
		</div><!-- .vbx-full-pane -->
		
		<h2>Call Recording</h2>
		<div class="radio-table">
			<table>
				<tr class="radio-table-row first <?php echo ($record === 'record-from-start') ? 'on' : 'off' ?>">
					<td class="radio-cell">
						<input type="radio" class='dial-whom-selector-radio' name="record" value="record-from-start" <?php echo ($record === 'record-from-start') ? 'checked="checked"' : '' ?> />
					</td>
					<td class="content-cell">
						<h4>Enable</h4>
					</td>
				</tr>
				<tr class="radio-table-row last <?php echo ($record === 'do-not-record') ? 'on' : 'off' ?>">
					<td class="radio-cell">
						<input type="radio" class='dial-whom-selector-radio' name="record" value="do-not-record" <?php echo ($record === 'do-not-record') ? 'checked="checked"' : '' ?> />
					</td>
					<td class="content-cell">
						<h4>Disable</h4>
					</td>
				</tr>
			</table>
		</div>

</div><!-- .vbx-applet -->

	
