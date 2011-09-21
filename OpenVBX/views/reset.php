<div class="vbx-content-container">
		<div id="login" class="login-reset">
			<form action="<?php echo site_url('auth/reset'); ?>" method="post" class="vbx-form">

			<input type="hidden" name="login" value="1" />

			<fieldset class="vbx-input-container">
					<p class="instruct">Please provide the E-Mail Address for your account and we'll send you a new password.</p>
					<label class="field-label">E-Mail Address
					<input type="text" class="medium" name="email" value="" />
					</label>
			</fieldset>

			<button type="submit" class="submit-button"><span>Reset Password</span></button>

			<a class="remember-password" href="../auth/login">Remember your password?</a>

			</form>
		</div><!-- #login -->
</div><!-- .vbx-content-container -->
