<div class="usergroup-container">
	<input class="usergroup-id" type="hidden" autocomplete="off" name="<?php echo $name ?>_id" value="<?php echo $owner_id ?>" />
	<input class="usergroup-type" type="hidden" autocomplete="off" name="<?php echo $name ?>_type" value="<?php echo $owner_type ?>" />
	<p class="<?php if(empty($owner_id)): ?>placeholder<?php endif; ?> selected-usergroup"><?php echo $label ?></p>
	<div class="usergroup-picker" title="Select User or Group"><a class="action choose"><span class="replace"><?php echo $label ?></span></a></div>
</div>
