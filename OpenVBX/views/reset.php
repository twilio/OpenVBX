<script type="text/javascript">
$().ready(function() {
	$('#iEmail').focus();
	$("form").validate({
		rules: {
			email: {required: true,	minlength: 2}
		},
		messages: {
			email: {required: "E-Mail required",	minlength: "E-Mail too short"}
		}
	});
});
</script>

<div class="vbx-content-container">

		<div id="login">
		<form action="<?php echo site_url('auth/reset'); ?>" method="post" class="vbx-form">

		<input type="hidden" name="login" value="1" />

		<fieldset class="vbx-input-container">
				<p class="instruct">Please provide the E-Mail Address for your account and we'll send you a new password.</p>
				<label class="field-label">E-Mail Address
				<input type="text" class="medium" name="email" value="" />
				</label>
		</fieldset>

		<?php if ($voicevault_enabled): ?>
		<br />

		<fieldset class="vbx-input-container">
			<p class="instruct"><input type="checkbox" name="phone" /> Reset your password by phone</p>

			<p class="voicevault-pw">
				<img src="<?php echo base_url(); ?>assets/i/voicevault-logo-big.png" />
			</p>
		</fieldset>
		<?php endif; ?>

		<button type="submit" class="submit-button"><span>Reset Password</span></button>

		<a class="remember-password" href="../auth/login">Remember your password?</a>

		</form>

		</div><!-- #login -->

</div><!-- .vbx-content-container -->
