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
			<li><a href="#system-config">System Config</a></li>
			<li><a href="#about">About</a></li>
		</ul>
	</div><!-- .vbx-content-tabs -->
	
	<div id="vbx-settings-content">

		<?php
			if (isset($upgrade_notice) && $upgrade_notice === true)
			{
				$this->load->view('settings/upgrade-notice.php', compact(
					'current_version',
					'latest_version'
				));
			}
		?>
	
		<div id="vbx-settings-forms">
			<div id="settings-theme" class="vbx-tab-view">
				<?php $this->load->view('settings/site-theme'); ?>
			</div><!-- #settings-theme -->
	
			<div id="settings-plugins" class="vbx-tab-view" style="display: none;">
				<?php $this->load->view('settings/site-plugins'); ?>
			</div><!-- #settings-plugins -->

			<div id="settings-multi-tenant" class="vbx-tab-view" style="display: none;">
				<?php $this->load->view('settings/site-multi-tenant'); ?>
			</div><!-- #settings-multi-tenant -->

			<div id="settings-twilio-account" class="vbx-tab-view" style="display: none;">
				<?php $this->load->view('settings/site-twilio-account'); ?>
			</div><!-- #settings-twilio-account -->
	
		    <div id="settings-system-config" class="vbx-tab-view" style="display: none;">
				<?php $this->load->view('settings/site-system-config'); ?>
			</div><!-- #settings-system-config -->

			<div id="settings-about" class="vbx-tab-view" style="display: none;">
				<?php $this->load->view('settings/site-about'); ?>
			</div><!-- #settings-about -->
		</div><!-- #vbx-settings-forms -->
		
	</div><!-- #vbx-settings-content -->
</div><!-- .vbx-content-main -->
