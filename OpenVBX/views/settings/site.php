	<div class="vbx-content-main">

		<div class="vbx-content-tabs">
			<h2 class="vbx-content-heading">Settings</h2>
			<ul>
				<li><a href="#theme">Theme</a></li>
				<li><a href="#plugins">Plugins</a></li>
				<?php if($tenant_mode == Site::MODE_MULTI): ?>
				<li><a href="#multi-tenant">Tenants</a></li>
				<?php endif; ?>
				<li><a href="#twilio-account">Twilio Account</a></li>
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
				
				<!-- @todo: add setting to select male/female voice option for speech -->
				
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
			<form name="tenants" action="<?php echo site_url('settings/site/tenant') ?>#multi-tenant" method="POST" class="add-tenant-form vbx-form" autocomplete="off">
				<div class="vbx-input-complex vbx-input-container">
					<label for="tenant-admin-email" class="field-label">Adminstrator email:
						<input id="tenant-admin-email" type="text" name="tenant[admin_email]" value="" class="medium" />
					</label>
				</div>
				<div class="vbx-input-complex vbx-input-container">
					<label for"tenant-url-prefix" class="field-label">Tenant Name:
					    <input id="tenant-url-prefix" type="text" name="tenant[url_prefix]" value="" class="medium" />
					</label>
		        </div>
				<div class="vbx-input-complex vbx-input-container">
				<?php if (isset($connect_application_sid) && !empty($connect_application_sid['value'])): ?>
					<label for="auth-type" class="field-label">Authentication Type:
						<select class="medium" name="auth_type" id="auth-type">
							<option value="subaccount">Sub-Account</option>
							<option value="connect">Twilio Connect (OAuth)</option>
						</select>
					</label>
				<?php else: /* In case anybody foobars their Connect App sid we can fail gracefully */?>
					<div style="width: 50%; margin: 15px 0;">
						<p>You don&rsquo;t have a <a href="http://twilio.com/docs/connect">Twilio Connect</a> Application defined. Your Tenants will be created as a sub-account of your account.</p>
						<p>To create Tenants with Twilio Connect create a Connect Application in your account and enter the Application Sid in the &ldquo;Twilio Connect Application SID&rdquo; field in your Twilio Account Settings screen.</p>
					</div>
					<input type="hidden" name="auth_type" value="subaccount" />
				<?php endif; ?>
				</div>
				<div class="vbx-input-complex vbx-input-container">
				    <button class="add-tenant-button normal-button" type="submit"><span>Add tenant</span></button>
				</div>
			</form>
			<br class="clear" />

			<table class="vbx-items-grid">
				<tbody>
					<?php foreach($tenants as $tenant): ?>
					<tr class="items-row">
							<td class="url-tenant"><a href="<?php echo tenant_url('', $tenant->id) ?>"><?php echo tenant_url('', $tenant->id) ?></a></td>
							<td class="type-tenant"><?php 
								switch ($tenant->type) {
									case VBX_Settings::AUTH_TYPE_FULL:
										echo 'Full';
										break;
									case VBX_Settings::AUTH_TYPE_CONNECT:
										echo 'Twilio Connect';
										break;
									case VBX_Settings::AUTH_TYPE_SUBACCOUNT:
										echo 'Sub-Account';
										break;
								}
							?></td>
							<td class="edit-tenant"><a href="<?php echo site_url('settings/site/tenant/'.$tenant->id) ?>" class="edit action"><span class="replace">Edit</span></a></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php endif; ?>
		</div><!-- .vbx-tab-view -->


		<div id="settings-twilio-account" class="vbx-tab-view">
			<form name="vbx-settings" action="<?php echo site_url('settings/site') ?>#twilio-account" method="POST" class="vbx-settings-form vbx-form" autocomplete="off">
				<h3>Twilio Account</h3>
				<fieldset class="vbx-input-container">
					<label for="site-twilio-sid" class="field-label">Twilio SID
						<input id="site-twilio-sid" type="text" name="site[twilio_sid]" value="<?php echo @$twilio_sid['value'] ?>" class="medium" />
					</label>
					<label for="site-twilio-token" class="field-label">Twilio Token
						<input id="site-twilio-token" type="password" name="site[twilio_token]" value="<?php echo @$twilio_token['value'] ?>" class="medium" />
					</label>
					<label for="site-twilio-application-sid" class="field-label">Twilio Client Application SID
						<input id="site-twilio-application-sid" type="text" name="site[application_sid]" value="<?php echo @$application_sid['value']; ?>" class="medium" />
						<p class="instruction">This Sid identifies your install for the purposes of making<br />and receiving calls with <a href="http://www.twilio.com/api/client">Twilio Client</a>.</p>
					</label>
					<label for="site-from-email" class="field-label">From Email
						<input id="site-from-email" type="text" name="site[from_email]" value="<?php echo @$from_email['value'] ?>" class="medium" />
						<p class="instruction">This is the email address which which all outbound emails<br />from OpenVBX install will be addressed.</p>
					</label>
				</fieldset>
			<?php if($tenant_mode == Site::MODE_MULTI): ?>
				<h3>Twilio Connect Settings</h3>
				<fieldset class="vbx-input-container">
					<p>This Sid identifies your install for the purposes of using<br />Twilio Connect to authorize your Tenant accounts.</p>
					<br />
					<label for="site-twilio-connect-application-sid" class="field-label">Twilio Connect Application SID
						<input id="site-twilio-connect-application-sid" type="text" name="site[connect_application_sid]" value="<?php echo @$connect_application_sid['value']; ?>" class="medium" />
						<p class="instruction">Leave blank to not use Twilio Connect. Changing an existing<br />Sid will invalidate any existing Connect authorizations.</p>
						<p class="instruction">The Connect Application will be checked and updated for the<br />proper callback urls on save.</p>
					</label>
				</fieldset>
			<?php endif; ?>
				<button class="submit-button" type="submit"><span>Update</span></button>
			</form>
		</div><!-- .vbx-tab-view -->


		<div id="settings-about" class="vbx-tab-view">
			<h3>About</h3>
			<ul>
				<li>Current Version: <?php echo OpenVBX::version() ?></li>
			<?php if ($tenant->id == VBX_PARENT_TENANT): /* if parent tenant */ ?>
				<li>Schema Version: <?php echo OpenVBX::schemaVersion() ?></li>
				<li>Latest Schema Available: <?php echo OpenVBX::getLatestSchemaVersion(); ?></li>
				<li>Database configuration: <?php echo "{$server_info['mysql_driver']}://{$this->db->username}@{$this->db->hostname}/{$this->db->database}" ?></li>
				<li>Rewrite enabled: <?php echo $rewrite_enabled['value']? 'Yes' : 'No' ?></li>
			<?php endif; /* if parent tenant */ ?>
			</ul>

<?php if ($tenant->id == VBX_PARENT_TENANT): /* if parent tenant 2 */ ?>
			<h3>Server Info</h3>
			<ul>
				<li>Apache Version: <?php echo $server_info['apache_version']; ?></li>
				<li>PHP Version: <?php echo $server_info['php_version']; ?></li>
				<li>MySQL Version: <?php echo $server_info['mysql_version']; ?> using <?php echo $server_info['mysql_driver']; ?> driver</li>
			</ul>
<?php endif; /* if parent tenant 2 */ ?>
			<br />
			
			<p>Thanks to everyone involved, you made it better than envisioned!</p>

			<?php require_once 'license.php' ?>
		</div>

	</div><!-- .vbx-content-main -->
