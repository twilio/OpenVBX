<?php
	// if the user has no incoming phone numbers or
	// is a trial account this will be flipped to true
	$dial_disabled = false;
?>

<form id="make-call-form" action="" method="POST">
	<fieldset>
		<legend>From</legend>
		
		<label class="field-label"><span class="label-text">Using</span>
			<div id="client-mode-status">
				<a id="client-mode-button" class="enabled" href="">Browser</a>
				<a id="phone-mode-button" class="disabled" href="">Phone</a>
			</div>
		</label>
		
		<div id="callerid-container">
			<?php $this->load->view('dialer/numbers'); ?>
		</div><!-- #callerid-container -->
	</fieldset>
	
	<fieldset>
		<legend>To</legend>
				
		<label class="field-label"><span class="label-text">To</span>
			<input id="dial-phone-number" type="text" placeholder="Phone number" />
		</label>
	</fieldset>
	
	<button id="dial-input-button" type="submit" <?php echo ($dial_disabled ? ' disabled="disabled"' : ''); ?>><span class="button-text">Dial</span></button>	
</form><!-- #make-call-form -->

<?php if (count($users)): /* user-list */ ?>
						
	<ul id="client-ui-user-list">
		<?php foreach ($users as $user): ?>
			<li id="user-<?php echo $user->id; ?>" class="user-item no-icons"><!-- not handled, introduces too much ui clutter
				<span class="status-icon enabled-phone" title="Phone Enabled"></span>
				<span class="status-icon disabled-client" title="Client Disabled"></span>-->
				<span class="user-name"><?php echo $user->first_name.' '.$user->last_name; ?></span>
				<button class="user-dial-button"<?php echo ($dial_disabled ? ' disabled="disabled"' : ''); ?>><span class="button-text">Dial</span></button>
				<input type="hidden" name="email" value="<?php echo $user->email; ?>" />
			</li>
		<?php endforeach; ?>
	</ul><!-- #client-ui-user-list -->
	
<?php endif; /* user-list */ ?>