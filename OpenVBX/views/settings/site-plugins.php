<h3>Plugins</h3>

<table class="vbx-items-grid">
	<thead>
        <tr class="items-head">
			<th class="plugin-name">Name</th>
			<th class="plugin-author">Author</th>
			<th class="plugin-desc">Description</th>
			<th class="plugin-path">Installed Path</th>
			<th class="plugin-config">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($plugins as $plugin): ?>
		<tr class="items-row">
			<td><?php echo $plugin['name'] ?></td>
			<td><?php echo $plugin['author'] ?></td>
			<td><?php echo $plugin['description'] ?></td>
			<td><?php echo $plugin['plugin_path'] ?></td>
			<td><a class="edit action" href="<?php echo site_url('config/'.$plugin['dir_name']); ?>"><span class="replace">Configure</span></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table><!-- .vbx-items-grid -->