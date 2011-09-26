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
			<input type="hidden" name="conf-id" value="<?php echo AppletInstance::getValue('conf-id', uniqid('conf_')) ?>" />
		</fieldset>
		</div><!-- .vbx-full-pane -->

		<h2>Note</h2>
		<p>Twilio and Tropo numbers use seperate channels for conferences. You cannot access the same conference through a Tropo and a Twilio number.</p>

</div><!-- .vbx-applet -->

	
