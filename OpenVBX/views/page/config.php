<div class="vbx-content-main">

		<?php if(!is_file($script)): ?>
		<?php $store = PluginData::getKeyValues(); ?>
		<div class="vbx-content-menu vbx-content-menu-top">
			<h2 class="vbx-content-heading">Plugin: <?php echo $info['name']; ?></h2>
		</div>

		<h3>Details</h3>
		<table class="vbx-items-grid">
		<tbody>
			<tr class="items-row">
				<td style="width: 120px;">Plugin ID</td>
				<td><?php echo $info['plugin_id']; ?></td>
			<tr>
			<tr class="items-row">
				<td style="width: 120px;">Description</td>
				<td><?php echo $info['description']; ?></td>
			<tr>
			<tr class="items-row">
				<td style="width: 120px;">Author</td>
				<td><?php echo $info['author']; ?></td>
			<tr>
			<?php if(isset($info['url'])): ?>
			<tr class="items-row">
				<td style="width: 120px;">Project</td>
				<td><a href="<?php echo $info['url']?>"><?php echo $info['url']; ?></a></td>
			<tr>
			<?php endif; ?>
			<tr class="items-row">
				<td style="width: 120px;">Path</td>
				<td><?php echo $info['plugin_path']; ?></td>
			<tr>
		</tbody>
		</table>
		<?php else: ?>

		<?php include_once($script); ?>

		<?php endif; ?>

</div><!-- .vbx-content-main -->
