<div class="vbx-content-main vbx-flows">

		<div class="vbx-content-menu vbx-content-menu-top">
			<h2 class="vbx-content-heading">Flows</h2>
			<?php if(!empty($items)): ?>
			<ul class="flows-menu vbx-menu-items-right">
				<li class="menu-item"><button class="add-button add-flow" type="button"><span>New Flow</span></button></li>
			</ul>
			<?php endif; ?>
			<?php echo $pagination; ?>
		</div><!-- vbx-content-menu -->

		<?php if(!empty($items)): ?>

		<div class="vbx-table-section">
		<table id="flows-table" class="vbx-items-grid">
			<thead>
				<tr class="items-head">
					<th class="flow-name">Name</th>
					<th class="flow-numbers">Phone Numbers</th>
					<th class="flow-voice">Call Flow</th>
					<th class="flow-sms">SMS Flow</th>
					<th class="flow-delete">Delete</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($items as $item): ?>
				<tr id="flow-<?php echo $item['id']?>" class="items-row <?php if(in_array($item['id'], $highlighted_flows)): ?>highlight-row<?php endif; ?>">
					<td>
						<span class="flow-name-display"><?php echo $item['name']; ?></span>
						<span class="flow-name-edit" style="display: none;">
							<input type="text" name="flow_name" value="<?php echo $item['name'] ?>" data-orig-value="<?php echo $item['name']; ?>"/>
							<button name="save" value="Save" data-action="/flows/edit/<?php echo $item['id']; ?>" class="submit-button"><span>Save</span></button>
							<span class="sep">|</span> <a href="#cancel" class="flow-name-edit-cancel">cancel</a>
						</span>
					</td>
					<?php if(empty($item['numbers'])): ?>
					<td>None</td>
					<?php else: ?>
					<td><?php echo implode(', ', $item['numbers']); ?></td>
					<?php endif; ?>
					<td><a href="<?php echo site_url("flows/edit/{$item['id']}"); ?>#flowline/start"><?php echo is_null($item['voice_data'])? 'Create' : 'Edit' ?> Call Flow</a></td>
					<td><a href="<?php echo site_url("flows/sms/{$item['id']}"); ?>#flowline/start"><?php echo is_null($item['sms_data'])? 'Create' : 'Edit' ?> SMS Flow</a></td>
					<td class="flow-delete"><a href="flows/edit/<?php echo $item['id'];?>" class="trash action" title="Delete"><span class="replace">Delete</span></a></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table><!-- .vbx-items-grid -->
		</div><!-- .vbx-table-section -->

		<?php else: ?>

		<div class="vbx-content-container">
				<div class="flows-blank">
						<h2>Create a new flow.</h2>
						<p>Flows allow you to control what happens on a call, such as forwarding the call to other people, taking voicemails, playing audio or text to the caller, and more.  You can also build flows to handle SMS messages.</p>
						<button class="add-button add-flow" type="button"><span>New Flow</span></button>			
				</div>
			<div class="vbx-content-section">
			</div><!-- .vbx-content-section -->
		</div><!-- .vbx-content-container -->

		<?php endif; ?>

</div><!-- .vbx-content-main -->

<div id="dialog-templates" style="display: none">
	<div id="dAddFlow" title="Add New Flow" class="dialog">
		<form action="<?php echo site_url('flows'); ?>" method="post" class="vbx-form">
			<fieldset class="vbx-input-container">
			<label class="field-label">Flow Name
			<input type="text" name="name" class="medium" />
			</label>
			</fieldset>
		</form>
	</div>

	<div id="dDeleteFlow" title="Delete Flow?" class="dialog">
		<p>Are you sure you wish to delete this flow?</p>
	</div>

	<div id="dCopyFlow" title="Copy Flow" class="dialog">
		<form action="#" method="post" class="vbx-form">
			<fieldset class="vbx-input-container">
			<label class="field-label">Please enter a name for the new flow
			<input type="text" name="name" class="medium" />
			</label>
			</fieldset>
		</form>
	</div>
</div>