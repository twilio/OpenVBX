<div id="vbx-incoming-numbers" class="vbx-numbers-section">
	<table class="phone-numbers-table vbx-items-grid" data-type="incoming">
		<thead>
			<tr class="items-head">
				<th class="incoming-number-phone">Phone Number</th>
				<th class="incoming-number-flow">Call Flow</th>
				<th class="incoming-number-caps">Capabilities</th>
				<th class="incoming-number-delete">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php
		if (count($incoming_numbers)):
			foreach($incoming_numbers as $item): 
				$classname = 'items-row';
				if (in_array($item->id, $highlighted_numbers))
				{
					$classname .= ' highlight-row';
				}
				if ($item->id == 'Sandbox')
				{
					$classname .= ' sandbox-row';
				}
			?>
			<tr rel="<?php echo $item->id; ?>" class="<?php echo $classname; ?>">
				<td class="incoming-number-phone"> 
					<?php if ($item->id == 'Sandbox'): /* Sandbox */?>
						<span class="sandbox-label">SANDBOX</span>
					<?php elseif ($item->phone_formatted != $item->name): /* Sandbox */ ?>
						<span class="number-label"><?php echo $item->name; ?></span>
					<?php endif; /* Sandbox */ ?>
					<?php 
						echo $item->phone; 
					?>
				</td>
				<td class="incoming-number-flow">
					<?php
						$settings = array(
							'name' => 'flow_id',
							'id' => 'flow_select_'.$item->id
						);
						echo t_form_dropdown($settings, $flow_options, $item->flow_id);
					?>
					<span class="status"><?php echo $item->status ?></span>
				</td>
				<td class="incoming-number-caps">
					<?php 
						if (!empty($item->capabilities))
						{
							echo implode(', ', $item->capabilities);
						}
					?>
				</td>
				<td class="incoming-number-delete">
					<a href="numbers/delete/<?php echo $item->id; ?>" class="action trash delete"><span class="replace">Delete</span></a>
				</td>
			</tr>
		<?php 
			endforeach; 
		else:
			?>
			<tr class="items-row null-row">
				<td colspan="4">You have no numbers assigned to this OpenVBX install.</td>
			</tr>
			<?php
		endif;
		?>
		</tbody>
	</table><!-- .vbx-items-grid -->
</div><!-- /.vbx-numbers-section -->