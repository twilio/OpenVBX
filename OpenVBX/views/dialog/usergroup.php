<div class="usergroup-dialog">
	<div class="users-and-groups-pane">
		<table class="users-and-groups-table">
		<tbody>
		
			<?php 
				foreach ($users_and_groups as $user_or_group) 
				{
					if ($user_or_group instanceof VBX_User)
					{
			?>
						<tr rel="user_<?php echo $user_or_group->id ?>" class="user">
							<td style="text-align: center;"><img src="<?php echo $asset_root; ?>/i/user-group-picker-person-icon.png" width="24" height="21" /></td>
							<td><?php echo $user_or_group->first_name ?> <?php echo $user_or_group->last_name ?></td>
							<td><?php echo $user_or_group->email ?></td>
							<td style="text-align: right; padding-right: 15px;"><a class="edit-link edit-user" href="">Edit</a></td>
						</tr>
			<?php
					}
					else
					{
			?>
						<tr rel="group_<?php echo $user_or_group->id ?>" class="group">
							<td style="text-align: center;"><img src="<?php echo $asset_root; ?>/i/user-group-picker-group-icon.png" width="32" height="22" /></td>
							<td colspan="2"><?php echo $user_or_group->name ?></td>
							<td style="text-align: right; padding-right: 15px;"><a class="edit-link edit-group" href="">Edit</a></td>
						</tr>
			<?php
					}
				}
			?>
		
		</tbody>
		</table>
	</div>
</div>
