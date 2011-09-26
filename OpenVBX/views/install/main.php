<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Install OpenVBX</title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>/assets/c/install.css" />
	<script type="text/javascript" src="<?php echo base_url() ?>/assets/j/frameworks/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url() ?>/assets/j/plugins/jquery.validate.js"></script>
	<?php $this->load->view('js-init'); ?>
	<script type="text/javascript" src="<?php echo base_url() ?>/assets/j/install.js"></script>

</head>
<body>

	<div id="install-container">

	<h1 id="openvbx-logo"><a href="<?php echo site_url() ?>/"><span class="replace">OpenVBX</span></a></h1>

	<form id="install-form" method="post" action="<?php echo site_url('install/setup'); ?>">

	<p class="error ui-widget-overlay"><?php if(isset($error)) echo $error; ?></p>
	<div class="steps">



		<div id="step-1" class="step">
			<a target="_blank" class="help" href="http://openvbx.org/install#step1" title="Get help at OpenVBX.org">Help</a>
			<h1><span class="number">1.</span>Check Server</h1>
			<div class="step-desc">
				<p>OpenVBX requires a few things from your server before it can be installed.<br /> Check out our <a target="_blank" href="http://openvbx.org/install">installation guide</a> for help.</p>
			</div>
			<ul class="dependencies">
				<input type="hidden" name="step" value="1" />
				<?php foreach($tests as $test): ?>
				<li class="<?php echo ($test['pass'] ? 'pass' : 'fail') ?> <?php echo ($test['required'] ? 'required' : 'optional') ?>">
					<span class="req-status"><?php echo ($test['pass'] ? 'OK' : 'NO') ?></span>
					<p class="req-name"><?php echo $test['name']; ?></p>
					<p class="req-info"><?php echo $test['message'] ?></p>
				</li>
				<?php endforeach; ?>
			</ul>

			<div class="information">
				<p><strong>Heads up&hellip;</strong> have your database credentials and <br />API Account information handy.</p>
			</div>

		</div>




		<div id="step-2" class="step">
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
					<input id="iDatabasePassword" class="medium" type="password" name="database_password" value="<?php echo htmlspecialchars($password)?>" />
					</label>

			</fieldset>
		</div>




		<div id="step-3" class="step">
			<h1><span class="number">3.</span>API Accounts</h1>

			<ul class="install-tabs">
				<li><a href="#" id="install-tab-twilio-header">Twilio</a></li>
				<li><a href="#" id="install-tab-tropo-header">Tropo</a></li>
				<li><a href="#" id="install-tab-voicevault-header">VoiceVault</a></li>
			</ul>

			<div id="install-tab-twilio" class="install-tab">
				<a target="_blank" class="help" href="http://openvbx.org/install#step3" title="Get help at OpenVBX.org">Help</a>

				<p><strong>Twilio Account</strong></p>

				<p>&nbsp;</p>

				<p class="step-desc">Login to <a target="_blank" href="https://www.twilio.com/user/account/">your dashboard</a> for your Twilio SID and Token.</p>

				<fieldset>
					<input type="hidden" name="step" value="3" />

					<label for="iTwilioSID">Twilio SID
					<input id="iTwilioSID" class="medium" type="text" name="twilio_sid" value="<?php echo htmlspecialchars($twilio_sid)?>"  />
					</label>

					<label for="iTwilioToken">Twilio Token
					<input id="iTwilioToken" class="medium" type="password" name="twilio_token" value="<?php echo htmlspecialchars($twilio_token)?>" />
					</label>
				</fieldset>
			</div>

			<div id="install-tab-tropo" class="install-tab">
				<a class="help" href="http://www.tropo.com/" target="_blank">Help</a>

				<p><strong>Tropo Account</strong></p>

				<p>&nbsp;</p>

				<p class="step-desc">Register a <a target="_blank" href="https://www.tropo.com/">Tropo account</a> for your unique Tropo username and password.</p>

				<fieldset>
					<?php /** Updated, Disruptive Technologies, for Tropo VBX conversion **/ ?>

					<label for="iTropoUsername">Tropo Username
					<input id="iTropoUsername" class="medium" type="text" name="tropo_username" value="<?php echo htmlspecialchars($tropo_username)?>"  />
					</label>

					<label for="iTropoPassword">Tropo Password
					<input id="iTropoPassword" class="medium" type="password" name="tropo_password" value="<?php echo htmlspecialchars($tropo_passwod)?>" />
					</label>

					<label for="iPhonoApiKey">Phono API Key
					<input id="iPhonoApiKey" class="medium" type="password" name="phono_api_key" value="<?php echo htmlspecialchars($phono_api_key)?>" title="Register an account at phono.com to make calls via the Phono web client." />
					</label>

					<?php /** End Disruptive Technologies code **/ ?>
				</fieldset>
			</div>

			<div id="install-tab-voicevault" class="install-tab">
				<a class="help" href="http://www.voicevault.com/" target="_blank">Help</a>

				<p><strong>VoiceVault Account</strong></p>

				<p>&nbsp;</p>
				
				<p class="step-desc">Register a <a target="_blank" href="http://www.voicevault.com/">VoiceVault account</a> for access to your secure Voice Vault.</p>

				<fieldset>
					<?php /** Updated, Disruptive Technologies, for Tropo VBX conversion **/ ?>

					<label for="iVoiceVaultUsername">VoiceVault Username
					<input id="iVoiceVaultUsername" class="medium" type="text" name="voicevault_username" value="<?php echo htmlspecialchars($voicevault_username)?>"  />
					</label>

					<label for="iVoiceVaultPassword">VoiceVault Password
					<input id="iVoiceVaultPassword" class="medium" type="password" name="voicevault_password" value="<?php echo htmlspecialchars($voicevault_password)?>" />
					</label>

					<label for="iVoiceVaultConfiguration">VoiceVault Configuration ID
					<input id="iVoiceVaultConfiguration" class="medium" type="text" name="voicevault_config" value="<?php echo htmlspecialchars($voicevault_config)?>"  />
					</label>

					<label for="iVoiceVaultOrganisation">VoiceVault Organisation ID
					<input id="iVoiceVaultOrganisation" class="medium" type="password" name="voicevault_organisation" value="<?php echo htmlspecialchars($voicevault_organisation)?>" />
					</label>

					<?php /** End Disruptive Technologies code **/ ?>
				</fieldset>
			</div>

		</div>


		<div id="step-4" class="step">
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




		<div id="step-5" class="step">
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
					<input id="iAdminPw" class="medium" type="password" name="admin_pw" />
					</label>

					<label for="iAdminPw">Confirm Password
					<input id="iAdminPw2" class="medium" type="password" name="admin_pw2" />
					</label>

			</fieldset>
		</div>




		<div id="step-6" class="step">
			<h1>Installation Complete!</h1>

			<p class="step-desc">Thanks for choosing OpenVBX, enjoy.</p>

			<a id="login-openvbx" href="<?php echo site_url() ?>">Login &raquo;</a>

			<fieldset>
				<input type="hidden" name="step" value="6" />
			</fieldset>
		</div>




		<?php endif; ?>
	</div>



	<div class="navigation">
		<button class="next">Next &raquo;</button>
		<button id="bInstall" class="submit">Install</button>
		<button class="prev">&laquo; Prev</button>
	</div>

	</form>

	</div><!-- #install-container -->

</body>
</html>
