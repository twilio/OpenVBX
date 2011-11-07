<?php 
	 $ci =& get_instance();
	 $is_admin = $ci->session->userdata('is_admin'); 
?>

<div class="vbx-content-main">

	<div class="vbx-content-menu vbx-content-menu-top">
		<?php 
			if (!empty($content_menu_url)) 
			{
				echo '<a class="back-link" href="'.$content_menu_url.'">&laquo; Back to Accounts</a>';
			}
		?>
		<h2 class="vbx-content-heading">Edit Account</h2>
	</div><!-- .vbx-content-menu -->

	<div class="vbx-content-section">
		<h3><?php echo $account_title; ?></h3>
		
		<form class="vbx-form" action="<?php echo site_url('account/edit/'.$user->id); ?>" method="post">

		<?php if(isset($message_edit)): ?>
			<p class="message"><?php echo $message_edit ?></p>
		<?php endif; ?>

		<?php if(isset($error_edit)): ?>
			<p class="error"><?php echo $error_edit ?></p>
		<?php endif; ?>

			<fieldset class="vbx-input-container">
				<label class="field-label">First Name
					<input type="text" class="medium" name="first_name" value="<?php echo $user->first_name; ?>" />
				</label>
				<label class="field-label">Last Name
					<input type="text" class="medium" name="last_name" value="<?php echo $user->last_name; ?>" />
				</label>
				<label class="field-label">E-Mail Address
					<input type="text" class="medium" name="email" value="<?php echo $user->email; ?>" />
				</label>
			</fieldset>
				
			<button type="submit" class="inline-button submit-button"><span>Save</span></button>
			<button type="button" class="change-password inline-button normal-button"><span>Change password</span></button>

		</form>

<?php if ($current_user->is_admin && $user->id != $current_user->id): ?>
		<div id="user-meta" style="clear: both; margin-top: 75px">
			<p>Only administrators see the information below</p>
			<hr />
			
			<h3>Devices</h3>
			
			<table class="vbx-items-grid">
				<thead>
					<tr class="items-head">
						<th>Device Name</th>
						<th>Number</th>
						<th>SMS</th>
						<th>Active</th>
					</tr>
				</thead>
				<tbody>
				<?php if (count($user->devices)): ?>
					<?php foreach ($user->devices as $device): ?>
						<tr class="items-row">
							<td><?php echo $device->name; ?></td>
							<td><?php echo $device->value; ?></td>
							<td><?php echo ($device->sms ? 'active' : 'inactive'); ?></td>
							<td><?php echo ($device->is_active ? 'active' : 'inactive'); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else: /* count($user->devices) */ ?>
					<tr class="items-row">
						<td colspan="100">This user has no devices</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
<?php endif; /* $current_user->is_admin */ ?>

	</div><!-- .vbx-content-section -->
</div><!-- .vbx-content-main -->

<form id="dialog-password" style="display: none;" class="dialog vbx-form" action="<?php echo site_url('account/password/'.$user->id); ?>" method="post" title="Change Password">
	<div class="hide error-message"></div>
	<fieldset class="vbx-input-container">
		<label class="field-label">Old Password
			<input type="password" class="medium" name="old_pw" />
		</label>
		<label class="field-label">New Password
			<input type="password" class="medium" name="new_pw1" />
		</label>
		<label class="field-label">Re-type New Password
			<input type="password" class="medium" name="new_pw2" />
		</label>
	</fieldset>
</form>
