<div class="vbx-content-main">

		<div class="vbx-content-menu vbx-content-menu-top">
			<h2 class="vbx-content-heading">Users</h2>
			<ul class="user-groups-menu vbx-menu-items-right">
				<li class="menu-item"><button id="button-add-user" class="inline-button add-button"><span>Add User</span></button></li>
				<li class="menu-item"><button id="button-add-group" class="inline-button add-button"><span>Add Group</span></button></li>
			</ul>
		</div><!-- .vbx-content-menu -->
			
		<div class="yui-ge accounts-section">
			<div class="yui-u first">	

				<div id="user-container">
				<h3>Users</h3>
				<p>Drag a user into a group to add them.</p>

				<ul class="user-list">
				<?php $admin = OpenVBX::getCurrentUser(); ?>
				<?php if(isset($users)): 
					foreach($users as $user): ?>
				<li class="user" rel="<?php echo $user->id ?>">
					<div class="user-utilities">
						<img class="gravatar" src="<?php
							if ($gravatars)
							{
								echo gravatar_url($user->email, 30, $default_avatar);
							}
							else
							{
								echo $default_avatar;
							}
						?>" width="30" height="30" />
						<?php if($user->id != $admin->id): ?>
						<a class="user-edit" href="<?php echo site_url('/account/user/'.$user->id); ?>"><span class="replace">Edit</span></a>
						<a class="user-remove" href="#remove"><span class="replace">Remove</span></a>
						<?php endif; ?>
					</div>
					<div class="user-info">
						<p class="user-name"><?php echo $user->first_name; ?> <?php echo $user->last_name; ?></p>
						<p class="user-email"><?php echo $user->email ?></p>
						<?php if ($user->is_admin): ?>
							<p class="user-administrator">Administrator</p>
						<?php endif; ?>
					</div>
				</li>
				<?php 
					endforeach; 
				endif; ?>
				<li class="user" rel="prototype" style="display:none;">
					<div class="user-utilities">
						<img class="gravatar" src="<?php echo $default_avatar; ?>" width="30" height="30" />
						<a class="user-edit" href="#edit"><span class="replace">Edit</span></a>
						<a class="user-remove" href="#remove"><span class="replace">Remove</span></a>
					</div>
					<div class="user-info">
						<p class="user-name">(prototype)</p>
						<p class="user-email"></p>
					</div>
				</li>
				</ul>
				</div><!-- #user-container -->

			</div><!-- .yui-u .first -->
			
			<div class="yui-u">

				<div id="group-container">
				<h3>Groups</h3>
				<p>Select a group to view the user list. Drag users to reorder the group.</p>

				<ul class="group-list">
				<?php if(isset($groups)) foreach($groups as $group_id => $group): ?>
					<li class="group" rel="<?php echo $group_id ?>">
							<img class="group-counter-loader hide" src="<?php echo asset_url('assets/i/ajax-loader-circle.gif'); ?>" alt="loading" />
							<span class="group-counter"><?php echo count($group->users) ?></span>

							<div class="group-utilities">
								<a class="group-edit" href="#edit">Edit Group</a>
								<a class="group-remove" href="#remove">Remove Group</a>
							</div>

							<div class="group-info">
								<p class="group-name"><?php echo $group->name; ?></p>
							</div>

							<ul class="members">
							<?php foreach($group->users as $user): ?>
								<li rel="<?php echo $user->user_id; ?>">
									<?php if(!empty($user->first_name)) : ?>
									<span><?php echo $user->first_name; ?> <?php echo $user->last_name; ?></span>
									<?php else: ?>
									<span><?php echo $user->email; ?></span>
									<?php endif;?>
									<a class="remove">Remove</a>
								</li>
							<?php endforeach; ?>
							</ul>

					</li><?php endforeach; ?>

					<li class="group" rel="prototype" style="display:none;">
						<span class="group-counter">0</span>
						<div class="group-utilities">
							<a class="group-edit" href="#">Edit Group</a>
							<a class="group-remove" href="#">Remove Group</a>
						</div>

						<div class="group-info">
							<p class="group-name">(Prototype)</p>
						</div>
						<ul class="members"></ul>
					</li>
				</ul>
				</div><!-- #group-container -->

			</div><!-- .yui-u -->

		</div><!-- .yui-ge 3/4, 1/4 -->

</div><!-- .vbx-content-main -->

<div id="accounts-dialogs" style="display: none;">
	<div id="dialog-invite-user" title="Invite User" class="hide dialog">
		<div class="error-message hide"></div>
		<form class="vbx-form" onsubmit="return false;">
			<fieldset class="vbx-input-container">
			<label class="field-label">Email
				<input type="text" class="medium" name="email" value="" />
			</label>
			</fieldset>
		</form>
	</div>

	<div id="dialog-google-app-sync" title="Use Google Apps for Domains" class="hide dialog">
		<div class="error-message hide"></div>
		<form class="vbx-form" onsubmit="return false;">
			<p>Enter your Email and Password for your Google Apps Domain</p>
			<fieldset class="vbx-input-container">
			<label class="field-label">Email
				<input type="text" class="medium" name="email" value="" />
			</label>
			<label class="field-label">Password
				<input type="password" class="medium" name="password" value="" />
			</label>
			</fieldset>
		</form>
	</div>

	<?php include("user_group_dialogs.php"); ?>

	<div id="dialog-delete" title="Delete" class="hide dialog">
		<div class="error-message hide"></div>
		<div id="dConfirmMsg">
			<p>Are you sure you want to delete?</p>
		</div>
	</div>
</div>