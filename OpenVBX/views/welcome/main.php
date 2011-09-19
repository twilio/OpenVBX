<div id="welcome-container">
	<h1 id="openvbx-logo"><a href="<?php echo site_url() ?>/"><span class="replace">OpenVBX</span></a></h1>

	<form id="install-form" method="post" action="">
		<div id="welcome-steps">
			<div class="error ui-widget-overlay"><?php if(isset($error)) echo $error; ?></div>

			<div class="steps">
				
				<div class="step next">
					<a target="_blank" class="help" href="http://openvbx.org/install#upgrade" title="Get help at OpenVBX.org">Help</a>
					<h1>Connect</h1>
					<div class="step-desc">
						<p>You need to connect your OpenVBX Account with your Twilio Account.</p>
			            <div class="upgrade-warning">
							<p>If you do not have a Twilio Account you can set up an Account during the next step.</p>
						</div>
					</div><!-- .step-desc -->
				</div><!-- .step -->

<?php

/*
if $data['tenant_sid'] is present and == 'unauthorized_client' then do not allow user to proceed, the user declined to give
OpenVBX access to their account.

Add new div here that has a back button, but no forward button.

*/

?>
				<div class="step submit">
					<a target="_blank" class="help" href="http://openvbx.org/install#connect" title="Get help at OpenVBX.org">Help</a>
					<h1>Connect Complete</h1>
					<div class="step-desc">
						<p>Your install is now ready!</p>
					</div><!-- .step-desc -->
				</div><!-- .step -->
				
			</div><!-- .steps -->

	<?php if (!isset($error)): ?>
			<div class="navigation">
				<button class="prev">&laquo; Previous</button>
				<button class="next">Continue &raquo;</button>
				<button class="submit">Continue to Inbox &raquo;</button>
			</div>
	<?php endif; ?>

		</div><!-- #welcome-steps -->
	</form>
</div><!-- #install-container -->