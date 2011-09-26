	<div class="vbx-content-main">

		<div class="vbx-content-tabs">
			<h2 class="vbx-content-heading">Settings</h2>
			<ul>
				<li><a href="#theme">Theme</a></li>
				<li><a href="#plugins">Plugins</a></li>
				<?php if($tenant_mode == Site::MODE_MULTI): ?>
				<li><a href="#multi-tenant">Tenants</a></li>
				<?php endif; ?>
				<?php /** Updated, Disruptive Technologies, for Tropo VBX conversion **/ ?>
				<li><a href="#api-accounts">API Accounts</a></li>
				<?php /** End Disruptive Technologies code **/ 
				/*
				<li><a href="#twilio-account">Twilio Account</a></li>
				<li><a href="#tropo-account">Tropo Account</a></li>
				*/ ?>
				<?php if($tenant_mode == Site::MODE_MULTI): ?>
				<li><a href="#system-config">System Config</a></li>
				<?php endif; ?>
				<li><a href="#about">About</a></li>
			</ul>
		</div><!-- .vbx-content-tabs -->

	    <div id="settings-system-config" class="vbx-tab-view">
			<h3>System Config</h3>
			<form name="vbx-system" action="<?php echo site_url('settings/site') ?>" method="POST" class="vbx-system-form vbx-form">
				<div class="vbx-input-complex vbx-input-container">
					<label for="rewrite" class="field-label">Do you want to enable mod_rewrite support?
						<select id="rewrite" class="medium" name="site[rewrite_enabled]">
							<?php foreach(array(0 => "No", 1 => "Yes" ) as $value => $option): ?>
							<option value="<?php echo $value ?>" <?php echo ($value == $rewrite_enabled['value'])? 'selected="selected"' : ''?>><?php echo $option ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</div>

				<div class="vbx-input-complex vbx-input-container">
					<label for="override" class="field-label">Hostname to use in recording URLs (must be a CNAME for api.twilio.com)
						<input class="medium" id="override" name="site[recording_host]" value="<?php echo @$recording_host["value"]; ?>">
				</div>

				<div class="vbx-input-complex vbx-input-container">
					<label for="tropo_hostname" class="field-label">Alternate Hostname to use in recording URLs (must be a CNAME for your OpenVBX server)
						<input class="medium" id="tropo_hostname" name="site[tropo_hostname]" value="<?php echo @$tropo_hostname["value"]; ?>">
				</div>

				<button class="submit-button" type="submit"><span>Update</span></button>
			</form>

		</div>

		<div id="settings-theme" class="vbx-tab-view">
			<h3>Theme</h3>
			<form name="vbx-settings" action="<?php echo site_url('settings/site') ?>#theme" method="POST" class="vbx-settings-form vbx-form">
				<fieldset class="vbx-input-container">
				<label for="site-theme" class="field-label">Choose a theme
					<select id="site-theme" class="medium" name="site[theme]">
						<?php foreach($available_themes as $available_theme): ?>
						<option value="<?php echo $available_theme ?>" <?php echo ($available_theme == $theme)? 'selected="selected"' : '' ?>><?php echo $available_theme ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				</fieldset>
				<button class="submit-button" type="submit"><span>Update</span></button>
			</form>
		</div><!-- .vbx-tab-view -->


		<div id="settings-plugins" class="vbx-tab-view">
			<h3>Plugins</h3>
			<table class="vbx-items-grid">
				<thead>
		            <tr class="items-head">
						<th class="plugin-name">Name</th>
						<th class="plugin-author">Author</th>
						<th class="plugin-desc">Description</th>
						<th class="plugin-path">Installed Path</th>
						<th class="plugin-config">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($plugins as $plugin): ?>
					<tr class="items-row">
						<td><?php echo $plugin['name'] ?></td>
						<td><?php echo $plugin['author'] ?></td>
						<td><?php echo $plugin['description'] ?></td>
						<td><?php echo $plugin['plugin_path'] ?></td>
						<td><a class="edit action" href="<?php echo site_url('config/'.$plugin['dir_name']); ?>"><span class="replace">Configure</span></a></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table><!-- .vbx-items-grid -->
		</div><!-- .vbx-tab-view -->


		<div id="settings-multi-tenant" class="vbx-tab-view">
			<?php if(isset($tenants)): ?>
			<h3>Tenants</h3>
			<form name="tenants" action="<?php echo site_url('settings/site/tenant') ?>#multi-tenant" method="POST" class="add-tenant-form vbx-form">
				<div class="vbx-input-complex vbx-input-container">
					<label for="tenant-admin-email" class="field-label">Adminstrator email:
						<input id="tenant-admin-email" type="text" name="tenant[admin_email]" value="" class="medium" />
					</label>
				</div>
				<div class="vbx-input-complex vbx-input-container">
					<label for"tenant-url-prefix" class="field-label">Tenant Name:
					    <input id="tenant-url-prefix" type="text" name="tenant[url_prefix]" value="" class="medium" />
					</label>
		            <fieldset class="vbx-input-complex vbx-input-container">
			            <label class="field-label-inline" for="create-subaccount">Create Subaccount
				            <input type="checkbox" id="create-subaccount" name="tenant[create_subaccount]" value="1" checked="checked"/>
			            </label>
		            </fieldset>

				    <button class="add-tenant-button normal-button" type="submit"><span>Add tenant</span></button>
				</div>
			</form>
			<br class="clear" />

			<table class="vbx-items-grid">
				<tbody>
					<?php foreach($tenants as $tenant): ?>
					<tr class="items-row">
							<td class="url-tenant"><a href="<?php echo tenant_url('', $tenant->id) ?>"><?php echo tenant_url('', $tenant->id) ?></a></td>
							<td class="edit-tenant"><a href="<?php echo site_url('settings/site/tenant/'.$tenant->id) ?>" class="edit action"><span class="replace">Edit</span></a></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php endif; ?>
		</div><!-- .vbx-tab-view -->


		<?php /** Updated, Disruptive Technologies, for Tropo VBX conversion **/ ?>

		<div id="settings-api-accounts" class="vbx-tab-view">
			<form name="vbx-settings" action="<?php echo site_url('settings/site') ?>#api-accounts" method="POST" class="vbx-settings-form vbx-form">
				<div class="settings-pane">
					<div class="twilio-logo-small"><span class="replace">Twilio Account</span></div>
					<!-- <h3>Twilio Account</h3> -->
					<fieldset class="vbx-input-container">
						<label for="site-twilio-sid" class="field-label">Twilio SID
							<input id="site-twilio-sid" type="text" name="site[twilio_sid]" value="<?php echo @$twilio_sid['value'] ?>" class="medium" />
						</label>
						<label for="site-twilio-token" class="field-label">Twilio Token
							<input id="site-twilio-token" type="password" name="site[twilio_token]" value="<?php echo @$twilio_token['value'] ?>" class="medium" />
						</label>
						<label for="site-twilio-application-sid" class="field-label">Twilio Client Application SID
							<input id="site-twilio-application-sid" type="text" name="site[application_sid]" value="<?php echo @$application_sid['value']; ?>" class="medium" />
						</label>
						<label for="site-from-email" class="field-label">From Email
							<input id="site-from-email" type="text" name="site[from_email]" value="<?php echo @$from_email['value'] ?>" class="medium" />
						</label>
					</fieldset>
				</div>
				<div class="settings-pane">
					<div class="tropo-logo-small"><span class="replace">Tropo Account</span></div>
					<!-- <h3>Tropo Account</h3> -->
					<fieldset class="vbx-input-container">
						<label for="site-tropo-username" class="field-label">Tropo Username
							<input id="site-tropo-username" type="text" name="site[tropo_username]" value="<?php echo @$tropo_username['value'] ?>" class="medium" />
						</label>
						<label for="site-tropo-password" class="field-label">Tropo Password
							<input id="site-tropo-password" type="password" name="site[tropo_password]" value="<?php echo @$tropo_password['value'] ?>" class="medium" />
						</label>
						<label for="site-phono-api-key" class="field-label">Phono API Key
							<input id="site-phono-api-key" type="text" name="site[phono_api_key]" value="<?php echo @$phono_api_key['value'] ?>" class="medium" />
						</label>
						<label for="site-from-email" class="field-label">From Email
							<input id="site-from-email" type="text" name="site[from_email]" value="<?php echo @$from_email['value'] ?>" class="medium" />
						</label>
					</fieldset>
				</div>
				<div class="settings-pane">
					<div class="voicevault-logo-small"><span class="replace">VoiceVault Account</span></div>
					<!-- <h3>VoiceVault Account</h3> -->
					<fieldset class="vbx-input-container">
						<label for="site-voicevault-username" class="field-label">VoiceVault Username
							<input id="site-voicevault-username" type="text" name="site[voicevault_username]" value="<?php echo @$voicevault_username['value'] ?>" class="medium" />
						</label>
						<label for="site-voicevault-password" class="field-label">VoiceVault Password
							<input id="site-voicevault-password" type="text" name="site[voicevault_password]" value="<?php echo @$voicevault_password['value'] ?>" class="medium" />
						</label>
						<label for="site-voicevault-config" class="field-label">VoiceVault Config ID
							<input id="site-voicevault-config" type="text" name="site[voicevault_config]" value="<?php echo @$voicevault_config['value'] ?>" class="medium" />
						</label>
						<label for="site-voicevault-organisation" class="field-label">VoiceVault Organisation ID
							<input id="site-voicevault-organisation" type="text" name="site[voicevault_organisation]" value="<?php echo @$voicevault_organisation['value'] ?>" class="medium" />
						</label>
						<label for="site-voicevault-number" class="field-label">Phone Number 
							<select id="site-voicevault-number" name="site[voicevault_number]" class="medium">
								<?php foreach ($numbers as $raw_number => $friendly_number): ?>
									<option value="<?php echo $raw_number ?>" <?php echo ($raw_number == $voicevault_number['value']) ? 'selected="selected"' : ''; ?>>
										<?php echo $friendly_number ?>
									</option>
								<?php endforeach; ?>
							</select>
						</label>
					</fieldset>
				</div>
				<p style="clear: both">&nbsp;</p>
				<button class="submit-button" type="submit"><span>Update</span></button>
			</form>
		</div><!-- .vbx-tab-view -->

		<?php /** End Disruptive Technologies code **/ ?>


		<?php /*<div id="settings-twilio-account" class="vbx-tab-view">
			<h3>Twilio Account</h3>
			<form name="vbx-settings" action="<?php echo site_url('settings/site') ?>#twilio-account" method="POST" class="vbx-settings-form vbx-form">
				<fieldset class="vbx-input-container">
					<label for="site-twilio-sid" class="field-label">Twilio SID
						<input id="site-twilio-sid" type="text" name="site[twilio_sid]" value="<?php echo @$twilio_sid['value'] ?>" class="medium" />
					</label>
					<label for="site-twilio-token" class="field-label">Twilio Token
						<input id="site-twilio-token" type="password" name="site[twilio_token]" value="<?php echo @$twilio_token['value'] ?>" class="medium" />
					</label>
					<label for="site-twilio-application-sid" class="field-label">Twilio Client Application SID
						<input id="site-twilio-application-sid" type="text" name="site[application_sid]" value="<?php echo @$application_sid['value']; ?>" class="medium" />
					</label>
					<label for="site-from-email" class="field-label">From Email
						<input id="site-from-email" type="text" name="site[from_email]" value="<?php echo @$from_email['value'] ?>" class="medium" />
					</label>
				</fieldset>
				<button class="submit-button" type="submit"><span>Update</span></button>
			</form>
		</div><!-- .vbx-tab-view -->

		<?php /** Updated, Disruptive Technologies, for Tropo VBX conversion **/ ?>
		
		<?php /*<div id="settings-tropo-account" class="vbx-tab-view">
			<h3>Tropo Account</h3>
			<form name="vbx-settings" action="<?php echo site_url('settings/site') ?>#tropo-account" method="POST" class="vbx-settings-form vbx-form">
				<fieldset class="vbx-input-container">
					<label for="site-tropo-username" class="field-label">Tropo Username
						<input id="site-tropo-username" type="text" name="site[tropo_username]" value="<?php echo @$tropo_username['value'] ?>" class="medium" />
					</label>
					<label for="site-tropo-password" class="field-label">Tropo Password
						<input id="site-tropo-password" type="password" name="site[tropo_password]" value="<?php echo @$tropo_password['value'] ?>" class="medium" />
					</label>
					<label for="site-from-email" class="field-label">From Email
						<input id="site-from-email" type="text" name="site[from_email]" value="<?php echo @$from_email['value'] ?>" class="medium" />
					</label>
				</fieldset>
				<button class="submit-button" type="submit"><span>Update</span></button>
			</form>
		</div>

		<?php /** End Disruptive Technologies code **/ ?>


		<div id="settings-about" class="vbx-tab-view">
			<h3>About</h3>
			<ul>
				<li>Current Version: <?php echo OpenVBX::version() ?></li>
				<li>Schema Version: <?php echo OpenVBX::schemaVersion() ?></li>
				<li>Latest Schema Available: <?php echo OpenVBX::getLatestSchemaVersion(); ?></li>
				<li>Database configuration: <?php echo "mysql://{$this->db->username}@{$this->db->hostname}/{$this->db->database}" ?></li>
				<li>Rewrite enabled: <?php echo $rewrite_enabled['value']? 'Yes' : 'No' ?></li>
			</ul>

			<p>Thanks to everyone involved, you made it better than envisioned!</p>

			<?php require_once 'license.php' ?>
		</div>

	</div><!-- .vbx-content-main -->
