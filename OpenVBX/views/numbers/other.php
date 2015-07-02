<h3 class="vbx-table-section-header vbx-header-other-numbers">Numbers used on other Domains: <a class="toggle-link"  href="#vbx-other-numbers"><span class="show">show</span><span class="hide">hide</span></a></h3>
<div id="vbx-other-numbers" class="vbx-numbers-section" style="display: none;">
	<div class="vbx-numbers-section-info">
		<p>These are numbers that have either a Url or SMS Url defined.</p> 
		<p>Click &ldquo;Import Number&rdquo; to assign the number to a flow in OpenVBX. Assigning the number to a flow will overwrite both its Url &amp; SMS Url.</p>
	</div>
	<table class="phone-numbers-table vbx-items-grid" data-type="other">
		<thead>
			<tr class="items-head">
				<th class="incoming-number-phone">Phone Number</th>
				<th class="incoming-number-flow"></th>
				<th class="incoming-number-caps">Capabilities</th>
				<th class="incoming-number-delete">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($other_numbers as $item): 
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
					?> <a href="#<?php echo $item->id; ?>" class="incoming-number-details-toggle toggle-link">details</a>
					<br />
					<ul id="other-details-<?php echo $item->id; ?>" class="incoming-number-other-detail" style="display: none;">
						<?php if (!empty($item->url)): ?>
							<li><b>Url:</b> <?php echo $item->url; ?></li>
						<?php endif; ?>
						<?php if (!empty($item->smsUrl)): ?>
							<li><b>SMS Url:</b> <?php echo $item->smsUrl; ?></li>
						<?php endif; ?>
					</ul>
				</td>
				<td class="incoming-number-flow">
					<?php
						$settings = array(
							'name' => 'flow_id',
							'id' => 'flow_select_'.$item->id
						);
						$flow_options['-'] = 'Import Number';
						echo t_form_dropdown($settings, $flow_options);
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
		<?php endforeach; ?>
		</tbody>
	</table>
</div><!-- /.vbx-numbers-section -->