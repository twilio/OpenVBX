<?php 
if(!isset($tenants)) { return; }
?>
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
	<?php endif; ?>
	</div>
	
	<div class="vbx-input-complex vbx-input-container">
	    <button class="add-tenant-button normal-button" type="submit"><span>Add tenant</span></button>
	</div>
	
	<?php if (!isset($connect_application_sid) || empty($connect_application_sid['value'])): ?>
		<div class="info" style="width: 50%;">
			<p>You don&rsquo;t have a <a href="http://twilio.com/docs/connect" onclick="window.open(this.href); return false;">Twilio Connect</a> Application defined. Your Tenants will be created as a sub-account of your account.</p>
			<p>To create Tenants with Twilio Connect create a Connect Application in your account and enter the Application Sid in the &ldquo;Twilio Connect Application SID&rdquo; field in your Twilio Account Settings screen.</p>
		</div>
		<input type="hidden" name="auth_type" value="subaccount" />
	<?php endif; ?>

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