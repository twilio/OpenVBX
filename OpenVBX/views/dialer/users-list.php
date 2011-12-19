<?php global $dial_disabled; ?>
<ul id="client-ui-user-list" style="margin-top: 0;">
	<?php foreach ($users as $user): ?>
		<li id="user-<?php echo $user->id; ?>" class="user-item no-icons">
			<?php 
				/* not handled, introduces too much ui clutter
				<span class="status-icon enabled-phone" title="Phone Enabled"></span>
				<span class="status-icon disabled-client" title="Client Disabled"></span> 
				*/ 
			?>
			<span class="user-name"><?php echo $user->first_name.' '.$user->last_name; ?></span>
			<button class="user-dial-button"<?php 
				echo (isset($dial_disabled) && $dial_disabled ? ' disabled="disabled"' : ''); 
			?>><span class="button-text">Call</span></button>
			<input type="hidden" name="email" value="<?php echo $user->email; ?>" />
		</li>
	<?php endforeach; ?>
</ul><!-- #client-ui-user-list -->