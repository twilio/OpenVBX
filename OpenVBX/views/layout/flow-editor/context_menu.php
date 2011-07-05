
		<div id="vbx-context-menu" class="context-menu">

				<div class="notify <?php echo (isset($error) && !empty($error))? '' : 'hide' ?>">
				<p class="message">
					<?php if(isset($error) && $error): ?>
						<?php echo $error ?>
					<?php endif; ?>
					<a href="" class="close action"><span class="replace">Close</span></a>
				</p>
				</div><!-- .notify -->


				<div class="flow-details">
					<h2 class="flow-name-title">
						<span class="flow-name"><?php echo $flow->name ?><a href="#flow-rename" id="flow-rename" class="action-mini" style="display: none;">edit</a></span>
						<span class="flow-name-edit" style="display: none;">
							<input type="text" name="name" value="<?php echo $flow->name; ?>" data-orig-value="<?php echo $flow->name; ?>" />
							<a id="flow-rename-cancel" class="action-mini" href="#flow-rename-cancel">cancel</a>
						</span>
						<?php echo empty($flow->numbers)? '' : '<span class="flow-number">'.$flow->numbers[0]; ?><?php echo count($flow->numbers) >= 2? ' and '.(count($flow->numbers) - 1).' more</span>': '' ?></h2>
					
					<input type="hidden" name="id" value='<?php echo $flow->id; ?>' />
					<input type="hidden" name="data" value="<?php echo json_encode($flow->data); ?>" />
				</div><!-- .flow-details -->


				<ul class="vbx-menu-items-right">
					<li class="menu-item"><a class="save-button link-button" href=""><span>Save</span></a></li>
					<li class="menu-item"><a class="close-button link-button navigate-away" href="<?php echo site_url('flows') ?>"><span>Close</span></a></li>
				</ul>

		</div><!-- #vbx-context-menu .context-menu -->

		<?php if(!empty($callerid_numbers) && count($callerid_numbers) > 1): ?>
		<select name="callerid" class="small hide">
			<?php foreach($callerid_numbers as $number): ?>
			<option value="<?php echo $number->phone ?>"><?php echo $number->phone ?></option>
			<?php endforeach; ?>
		</select>
		<?php elseif(!empty($callerid_numbers) && count($callerid_numbers) == 1): ?>
		<?php $c = $callerid_numbers[0]; ?>
		<?php if(isset($c->trial) && $c->trial == 1): ?>
		<input type="hidden" name="callerid" value="" class="small" />
		<?php else: ?>
		<input type="hidden" name="callerid" value="<?php echo $c->phone ?>" />
		<?php endif; ?>
		<?php endif; ?>
