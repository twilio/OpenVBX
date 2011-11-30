<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Install OpenVBX</title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>assets/c/install.css" />
	<script type="text/javascript">
		if (window != window.top) { window.top.location = window.location; }
	</script>
	<?php $this->load->view('js-init'); ?>
<?php if ($this->config->item('use_unminimized_js')): ?>
	<script type="text/javascript" src="<?php echo base_url() ?>assets/j/frameworks/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url() ?>assets/j/plugins/jquery.validate.js"></script>
	<script type="text/javascript" src="<?php echo base_url() ?>assets/j/steps.js"></script>
	<script type="text/javascript" src="<?php echo base_url() ?>assets/j/install.js"></script>
<?php else: ?>
	<script type="text/javascript" src="<?php echo base_url() ?>assets/min/?g=installjs"></script>
<?php endif; ?>
</head>
<body>
<div id="install-container">
	<h1 id="openvbx-logo"><a href="<?php echo site_url() ?>/"><span class="replace">OpenVBX</span></a></h1>

		<form id="install-form" method="post" action="<?php echo site_url('install/setup'); ?>" autocomplete="off">
			<div id="install-steps">
				<p class="error ui-widget-overlay"><?php if(isset($error)) echo $error; ?></p>
				<div class="steps">
<!-- step 1 -->
					<div id="step-1" class="step next">
						<a target="_blank" class="help" href="http://openvbx.org/install#step1" title="Get help at OpenVBX.org">Help</a>
						<h1><span class="number">1.</span>Check Server</h1>
						<div class="step-desc">
							<p>OpenVBX requires a few things from your server before it can be installed.<br /> 
								Check out our <a target="_blank" href="http://openvbx.org/install">installation guide</a> for help.</p>
						</div>
						<input type="hidden" name="step" value="1" />
						<?php
							$open = false;
							foreach ($tests as $k => $test):
							 	if (!$open): $open = true; ?>
								<ul class="dependencies">
							<?php endif; ?>
									<li class="<?php echo ($test['pass'] ? 'pass' : 'fail') ?> <?php echo ($test['required'] ? 'required' : 'optional') ?>">
										<span class="req-status"><?php echo ($test['pass'] ? 'OK' : 'NO') ?></span>
										<p class="req-name"><?php echo $test['name']; ?></p>
										<p class="req-info"><?php echo $test['message'] ?></p>
									</li>
							<?php if (($k+1) % 3 === 0 || empty($tests[$k+1])): $open = false; ?>
								</ul>
							<?php endif; ?>
						<?php endforeach; /* foreach $tests */ ?>

						<div class="information">
						<?php if ($pass): ?>
							<p><strong>Heads up&hellip;</strong> have your database credentials and <br />Twilio Account information handy.</p>
						<?php else: ?>
							<p><strong>Tests did not pass. Please correct the problems listed above before continuing.</strong></p>
						<?php endif; ?>
						</div>
					</div>
<!-- step 2 -->
					<div id="step-2" class="step next prev">
						<a target="_blank" class="help" href="http://openvbx.org/install#step2" title="Get help at OpenVBX.org">Help</a>
						<h1><span class="number">2.</span>Configure Database</h1>

<?php if(isset($pass) && $pass === true): ?>
						<fieldset>
								<input type="hidden" name="step" value="2" />

								<label for="iDatabaseHost">Hostname
									<input id="iDatabaseHost" class="medium" type="text" name="database_host" value="<?php echo htmlspecialchars($hostname)?>" />
									<span class="instruction">For example: localhost, or your ip address</span>
								</label>

								<label for="iDatabaseName">MySQL Database Name
									<input id="iDatabaseName" class="medium" type="text" name="database_name" value="<?php echo htmlspecialchars($database)?>" />
									<span class="instruction">Note: This database must already exist.</span>
								</label>

								<label for="iDatabaseUser">MySQL Username
									<input id="iDatabaseUser" class="medium" type="text" name="database_user" value="<?php echo htmlspecialchars($username)?>" />
								</label>

								<label for="iDatabasePassword">MySQL Password
									<input id="iDatabasePassword" class="medium" type="password" name="database_password" value="<?php echo htmlspecialchars($password)?>" autocomplete="off" />
								</label>
						</fieldset>
					</div>
<!-- step 3 -->
					<div id="step-3" class="step next prev">
						<a target="_blank" class="help" href="http://openvbx.org/install#step3" title="Get help at OpenVBX.org">Help</a>
						<h1><span class="number">3.</span>Connect to Twilio</h1>

						<p class="step-desc">Login to <a href="https://www.twilio.com/user/account/" onclick="window.open(this.href); return false;">your dashboard</a> for your Twilio SID and Token.</p>
						
						<p class="step-desc">To set up a Twilio Connect Application for this install visit your Account&rsquo;s <a href="http://www.twilio.com/user/account/connect/apps" onclick="window.open(this.href); return false;">Connect Applications Section</a> and create an Application.</p>

						<fieldset>
							<input type="hidden" name="step" value="3" />

								<label for="iTwilioSID">Twilio SID
									<input id="iTwilioSID" class="medium" type="text" name="twilio_sid" value="<?php echo htmlspecialchars($twilio_sid)?>"  />
								</label>

								<label for="iTwilioToken">Twilio Token
									<input id="iTwilioToken" class="medium" type="password" name="twilio_token" value="<?php echo htmlspecialchars($twilio_token)?>" autocomplete="off" />
								</label>
						</fieldset>
							
						<fieldset>
								<label for="iTwilioConnectSID">Twilio Connect Sid <i>(optional)</i>
									<input id="iTwilioConnectSID" class="medium" type="text" name="connect_application_sid" value="<?php echo htmlspecialchars($connect_application_sid); ?>" />
								</label>
						</fieldset>

					</div>
<!-- step 4 -->
					<div id="step-4" class="step next prev">
						<a target="_blank" class="help" href="http://openvbx.org/install#step4" title="Get help at OpenVBX.org">Help</a>
						<h1><span class="number">4.</span>Options</h1>
						<p class="step-desc">OpenVBX can send messages and notifications through email. Enter an E-Mail Address that you want to show up as the From address when OpenVBX sends messages.</p>

						<fieldset>
							<input type="hidden" name="step" value="4" />

								<label for="iFromEmail">Notifications will come from
									<input id="iFromEmail" class="medium" type="text" name="from_email" value="<?php echo htmlspecialchars($from_email)?>" />
									<span class="instruction">You'll be able to change this later in your OpenVBX Settings.</span>
								</label>

							<input type="hidden" name="theme" value="<?php echo htmlspecialchars($theme)?>" />
							<input type="hidden" name="rewrite_enabled" value="0" />
						</fieldset>
					</div>
<!-- step 5 -->
					<div id="step-5" class="step prev submit">
						<a target="_blank" class="help" href="http://openvbx.org/install#step5" title="Get help at OpenVBX.org">Help</a>
						<h1><span class="number">5.</span>Your Account</h1>

						<p class="step-desc">You will use your account to login to OpenVBX once this installation is complete.</p>

						<fieldset>
							<input type="hidden" name="step" value="5" />

								<label for="iAdminFirstName">First Name
									<input id="iAdminFirstName" class="medium" type="text" name="admin_firstname" value="<?php echo htmlspecialchars($firstname)?>" />
								</label>

								<label for="iAdminLastName">Last Name
									<input id="iAdminLastName" class="medium" type="text" name="admin_lastname" value="<?php echo htmlspecialchars($lastname)?>" />
								</label>

								<label for="iAdminEmail">E-Mail Address
									<input id="iAdminEmail" class="medium" type="text" name="admin_email" value="<?php echo htmlspecialchars($email)?>" />
									<span class="instruction">You will use this E-Mail Address to login to OpenVBX</span>
								</label>

								<label for="iAdminPw">Password
									<input id="iAdminPw" class="medium" type="password" name="admin_pw" autocomplete="off" />
								</label>

								<label for="iAdminPw">Confirm Password
									<input id="iAdminPw2" class="medium" type="password" name="admin_pw2" autocomplete="off" />
								</label>

						</fieldset>
					</div>
<!-- step 6 -->
					<div id="step-6" class="step">
						<h1>Installation Complete!</h1>

						<p class="step-desc">Thanks for choosing OpenVBX, enjoy.</p>

						<a id="login-openvbx" href="<?php echo site_url() ?>">Login &raquo;</a>

						<fieldset>
							<input type="hidden" name="step" value="6" />
						</fieldset>
					</div>
<?php endif; /* test pass check */ ?>
				</div>

				<div class="navigation">
					<button type="submit" class="next">Next &raquo;</button>
					<button type="submit" class="submit" id="bInstall">Install</button>
					<button class="prev">&laquo; Prev</button>
				</div>
			</div><!-- steps -->
		</form>
</div><!-- #install-container -->
</body>
</html>
