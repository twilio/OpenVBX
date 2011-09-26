<?php 
	 $ci =& get_instance();
	 $is_admin = $ci->session->userdata('is_admin'); 
?>

<div class="vbx-content-main">

		<div class="vbx-content-menu vbx-content-menu-top">
			<h2 class="vbx-content-heading">My Account</h2>
		</div><!-- .vbx-content-menu -->



		<div class="vbx-content-section">
				<form class="vbx-form" action="<?php echo site_url('account/edit'); ?>" method="post">

				<?php if(isset($message_edit)): ?>
				<p class="message"><?php echo $message_edit ?></p>
				<?php endif; ?>

				<?php if(isset($error_edit)): ?>
				<p class="error"><?php echo $error_edit ?></p>
				<?php endif; ?>

				<h3>General Account Settings</h3>
				<fieldset class="vbx-input-container">
				<label class="field-label">First Name
				<input type="text" class="medium" name="first_name" value="<?php echo $user->first_name; ?>" />
				</label>
				<label class="field-label">Last Name
				<input type="text" class="medium" name="last_name" value="<?php echo $user->last_name; ?>" />
				</label>
				<label class="field-label">E-Mail Address
				<input type="text" class="medium" name="email" value="<?php echo $user->email; ?>" />
				</label>
				</fieldset>

				<button type="submit" class="inline-button submit-button"><span>Save</span></button>
				<button type="button" class="change-password inline-button normal-button"><span>Change password</span></button>

				<p style="clear: both">&nbsp;</p>
				<h3>VoiceVault Settings</h3>
				<?php if ($claimant_id): ?>
				<p><strong>ID</strong>: <?php echo $claimant_id; ?></p>
				<p>&nbsp;</p>
				<button type="submit" class="inline-button submit-button voicevault-setup"><span>Re-Enroll</span></button>
				<?php else: ?>
				<p>VoiceVault not setup. Click the button below to setup your account.</p>
				<p>&nbsp;</p>
				<button type="submit" class="inline-button submit-button voicevault-setup"><span>Setup</span></button>
				<?php endif; ?>

				</form>

		</div><!-- #account-edit -->


</div><!-- .vbx-content-main -->


<form id="dialog-password" style="display: none;" class="dialog vbx-form" action="<?php echo site_url('account/password'); ?>" method="post" title="Change Password">
<div class="hide error-message"></div>
<fieldset class="vbx-input-container">
<label class="field-label">Old Password
<input type="password" class="medium" name="old_pw" />
</label>
<label class="field-label">New Password
<input type="password" class="medium" name="new_pw1" />
</label>
<label class="field-label">Re-type New Password
<input type="password" class="medium" name="new_pw2" />
</label>
</fieldset>
</form>
