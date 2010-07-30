<div class="vbx-content-main">

		<div class="vbx-content-menu vbx-content-menu-top">
			<a href="<?php echo site_url('settings/site') ?>#multi-tenant" class="back-link">&laquo; Back to Site Settings</a>
			<h2 class="vbx-content-heading">Edit Tenant</h2>
		</div><!-- .vbx-content-menu -->


		<div class="vbx-content-container">
				<form name="tenant-edit" action="<?php echo site_url('settings/site/tenant/'.$tenant->id) ?>" method="POST" class="vbx-tenant-form vbx-form">

				<div class="vbx-content-section" style="min-height:50px;">
					<div class="vbx-input-complex vbx-input-container">
					<label for="tenant-url-prefix" class="field-label-inline"><?php echo site_url('') ?>
						<input id="tenant-url-prefix" class="inline small" type="text" name="tenant[url_prefix]" value="<?php echo $tenant->url_prefix ?>" />
					</label>
					</div>
				</div>

				<div class="vbx-content-section">			
					<fieldset class="activate-tenant vbx-input-complex vbx-input-container">
					<label class="field-label-inline"><input id="active" type="radio" name="tenant[active]" value="1" <?php echo ($tenant->active == 1)? 'checked="checked"' : ''?> />Active</label>
					<label class="field-label-inline"><input id="inactive" type="radio" name="tenant[active]" value="0" <?php echo ($tenant->active == 0)? 'checked="checked"' : ''?> />Inactive</label>
					</fieldset>

					<fieldset id="tenant-settings" class="vbx-input-container">
						<label for="tenant-setting-twilio-sid" class="field-label">Twilio SID 
							<input id="tenant-setting-twilio-sid" class="medium" type="text" name="tenant_settings[twilio_sid]" value="<?php echo @$tenant_settings['twilio_sid']['value'] ?>" />
						</label>
						<label for="tenant-setting-twilio-token" class="field-label">Twilio Token 
							<input id="tenant-setting-twilio-token" class="medium" type="text" name="tenant_settings[twilio_token]" value="<?php echo @$tenant_settings['twilio_token']['value'] ?>" />
						</label>
						<label for="tenant-setting-from-email" class="field-label">From Email 
							<input id="tenant-setting-from-email" class="medium" type="text" name="tenant_settings[from_email]" value="<?php echo @$tenant_settings['from_email']['value'] ?>" />
						</label>
						<label for="tenant-setting-theme" class="field-label">Theme
					        <select id="tenant-setting-theme" class="medium" name="tenant_settings[theme]">
						        <?php foreach($available_themes as $available_theme): ?>
						        <option value="<?php echo $available_theme ?>" <?php echo ($available_theme == $tenant_settings['theme']['value'])? 'selected="selected"' : '' ?>><?php echo $available_theme ?></option>
						        <?php endforeach; ?>
					        </select>
						</label>
					</fieldset>
					<button class="submit-button"><span>Update</span></button>
				</div>
				</form>
		</div><!-- .vbx-content-container -->

</div><!-- .vbx-content-main -->
