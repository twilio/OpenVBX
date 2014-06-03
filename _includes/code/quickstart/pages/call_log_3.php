<div class="vbx-plugin">
	<h3>Call Log</h3>
	<p>Showing the last <?php echo $limit; ?> calls.</p>
	
	<table>
		<thead>
			<tr>
				<th>Number</th>
				<th>Start Time</th>
				<th>End Time</th>
				<th>Duration</th>
				<th>Called</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($calls as $call): ?>
			<tr>
				<td><?php echo number_text($call->from); ?></td>
				<td><?php echo format_date($call->start_time); ?></td>
				<td><?php echo format_date($call->end_time); ?></td>
				<td><?php echo $call->duration; ?> sec</td>
				<td><?php echo number_text($call->to); ?></td>
				<td><?php echo humanize($call->status); ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
</div>
