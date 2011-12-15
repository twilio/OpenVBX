<?php 
if(!isset($tenants)) { return; }
?>
<h3>Tenants</h3>

<form name="tenants" action="<?php echo site_url('settings/site/tenant') ?>#multi-tenant" method="post" class="add-tenant-form vbx-form" autocomplete="off">
	<fieldset>
	<?php if (!isset($connect_application_sid) || empty($connect_application_sid['value'])): ?>
		<div class="info notice">
			<p>You don&rsquo;t have a <a href="http://twilio.com/docs/connect" onclick="window.open(this.href); return false;">Twilio Connect</a> Application defined. Your Tenants will be created as a sub-account of your account.</p>
			<p>To create Tenants with Twilio Connect create a Connect Application in your account and enter the Application Sid in the &ldquo;Twilio Connect Application SID&rdquo; field in your <a href="#twilio-account">Twilio Account Settings</a> screen.</p>
		</div>
		<input type="hidden" name="auth_type" value="subaccount" />
	<?php endif; ?>
	
	<div class="vbx-input-complex vbx-input-container">
		<label for="tenant-admin-email" class="field-label">Adminstrator email:
			<?php
				$email_data = array(
					'name' => 'tenant[admin_email]',
					'id' => 'tenant-admin-email',
					'class' => 'medium'
				);
				echo t_form_input($email_data, '');
			?>
		</label>
	</div>
	
	<div class="vbx-input-complex vbx-input-container">
		<label for="tenant-url-prefix" class="field-label">Tenant Name:
			<?php
				$url_data = array(
					'name' => 'tenant[url_prefix]',
					'id' => 'tenant-url-prefix',
					'class' => 'medium'
				);
				echo t_form_input($url_data, '');
			?>
		</label>
    </div>
	
	<div class="vbx-input-complex vbx-input-container">
	<?php if (isset($connect_application_sid) && !empty($connect_application_sid['value'])): ?>
		<label for="auth-type" class="field-label">Authentication Type:
			<?php
				$params = array(
					'name' => 'auth_type',
					'id' => 'auth-type',
					'class' => 'medium'
				);
				$options = array(
					'subaccount' => 'Sub-Account',
					'connect' => 'Twilio Connect'
				);
				echo t_form_dropdown($params, $options);
			?>
		</label>
	<?php endif; ?>
	</div>
	</fieldset>
	<div class="vbx-input-complex vbx-input-container">
	    <button class="add-tenant-button normal-button" type="submit"><span>Add tenant</span></button>
	</div>

</form>

<br class="clear" />

<table class="vbx-items-grid">
	<tbody>
	<?php if (count($tenants)):
		foreach($tenants as $tenant): ?>
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
	<?php endforeach; 
		else: ?>
		<tr class="items-row"><td>There are no tenants.</td></tr>
	<?php endif; ?>
	</tbody>
</table>