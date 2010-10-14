
<!-- Empty Drop Zone -->

<?php if(empty($value)): ?>
<div class="empty-item flowline-item" title="<?php echo $label ?>">
	<div class="item-body">
		<?php echo $label ?>
	</div><!-- .item-body -->
	<input type="hidden" autocomplete="off" name="<?php echo $name ?>" value="" />
</div><!-- .flowline-item -->
<?php else: ?>

<!-- Filled Drop Zone -->
<div class="filled-item flowline-item" title="<?php echo $label ?>">
	<div class="item-body">
		<a href="#flowline/<?php echo $value ?>" class="item-box">
			<div class="<?php echo $applet ?>-icon applet-icon" style="background: url(<?php echo $icon_url ?>) no-repeat center center;">
				<span class="replace"><?php echo $label ?></span>
			</div>
			<span class="applet-item-name"><?php echo $label ?></span>
		</a>
		<div class="flowline-item-remove action-mini remove-mini">
			<span class="replace">remove</span>
		</div>
	</div><!-- .item-body -->
	<input type="hidden" autocomplete="off" name="<?php echo $name ?>" value="<?php echo $value ?>" />
</div><!-- .flowline-item -->
<?php endif; ?>
