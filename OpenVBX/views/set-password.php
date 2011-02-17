<div class="vbx-content-container">

	<div id="login">
		<form action="<?php echo site_url('auth/reset/'.$invite_code); ?>" method="post" class="vbx-form">

			<input type="hidden" name="login" value="1" />

			<fieldset class="vbx-input-container">
				<p class="instruct">Enter a new password below:</p>
				<label class="field-label">Password:
					<input type="password" class="medium" name="password" value="" />
				</label>
				<label class="field-label">Confirm Password:
					<input type="password" class="medium" name="confirm" value="" />
				</label>
			</fieldset>

			<button type="submit" class="submit-button"><span>Set Password</span></button>

			<a class="remember-password" href="../auth/login">Remember your password?</a>

		</form>

	</div><!-- #login -->

</div><!-- .vbx-content-container -->

</div>
