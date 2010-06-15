<div class="vbx-content-main">

	<div class="vbx-content-menu vbx-content-menu-top">
		<h2 class="vbx-content-heading">Phone Numbers</h2>
		<?php if((count($items) < 1 || count($items) == 1 && $items[0]['id'] == 'Sandbox')): ?>
		<?php else: ?>
		<ul class="phone-numbers-menu vbx-menu-items-right">
			<li class="menu-item"><button class="add-button add number"><span>Get a Number</span></button></li>
		</ul>
		<?php endif; ?>
	</div><!-- .vbx-content-menu -->


	<div class="vbx-content-container">
		<div class="numbers-blank <?php if(!(count($items) < 1 || count($items) == 1 && $items[0]['id'] == 'Sandbox')): ?>hide<?php endif; ?>">
			<h2>Hey, you don't have any of your own phone numbers!</h2>
			<p>You can get toll free numbers, or local numbers in nearly any area code, that people can use to call you.</p>
			<button class="add-button add number"><span>Get a Number</span></button>
		</div>
		<?php if(!empty($items)): ?>

		<div class="vbx-table-section">
			<table id="phone-numbers-table" class="vbx-items-grid">
				<thead>
					<tr class="items-head">
						<th class="incoming-number-phone">Phone Number</th>
						<th class="incoming-number-flow">Call Flow</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($items as $item): ?>
					<tr rel="<?php echo $item['id'] ?>" class="items-row <?php if(in_array($item['id'], $highlighted_numbers)): ?>highlight-row<?php endif;?> <?php echo ($item['id'] == 'Sandbox')? 'sandbox-row' :'' ?>">
						<td class="incoming-number-phone"><?php echo ($item['id'] == 'Sandbox')? '<span class="sandbox-label">SANDBOX</span>' : ''?><?php echo $item['phone'] ?> <?php echo !empty($item['pin'])? ' Pin: '.implode('-', str_split($item['pin'], 4)) : '' ?></td>
						<td class="incoming-number-flow">
							<select name="flow_id">
								<option value="">Connect a Flow</option>
								<?php foreach($item['flows'] as $flow): ?>
								<option value="<?php echo $flow->id?>" <?php echo ($flow->id == $item['flow_id'])? 'selected="selected"': ''?>><?php echo $flow->name ?></option>
								<?php endforeach; ?>
								<option value="">---</option>
								<option value="new">Create a new flow</option>
							</select>
							<span class="status"><?php echo $item['status'] ?></span>
						</td>
						<td class="incoming-number-delete">
							<?php if(empty($item['pin'])): ?>
							<a href="numbers/delete/<?php echo $item['id']; ?>" class="action trash delete"><span class="replace">Delete</span></a>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table><!-- .vbx-items-grid -->
		</div><!-- .vbx-table-section -->
		<?php else: ?>
		<div class="vbx-content-section">
		</div><!-- .vbx-content-section -->
		<?php endif; ?>
	</div><!-- .vbx-content-container -->
</div><!-- .vbx-content-main -->



<div id="dlg_change" title="Change the call flow?" class="dialog">
	<p>Changing the call flow will change how this number behaves.</p>
	<p>Are you sure you wish to change this number's call flow?</p>
</div>

<div id="dlg_delete" title="Delete phone number?" class="dialog">
	<p class="hide error-message"></p>
	<p>You can not undo this operation and will not be able to retrieve this number again.</p>
	<p>Are you sure you really want to delete this number?</p>
</div>

<div id="dlg_add" title="Get a new number" class="dialog">
	<div class="hide error-message"></div>

	<form class="number-order-interface content ui-helper-clearfix vbx-form" action="<?php echo site_url('numbers/add'); ?>" method="post">
		<input type="radio" id="iTypeLocal" name="type" value="local" checked="checked" />
		<label for="iTypeLocal" class="field-label-inline">Local</label>
		<input type="radio" id="iTypeTollFree" name="type" value="tollfree" />
		<label for="iTypeTollFree" class="field-label-inline">Toll-Free</label>

		<div id="pAreaCode" class="area-code">
			<fieldset class="vbx-input-complex vbx-input-container">
				<label for="iAreaCode" class="area-code-label">Area Code</label>
				<span id="area-code-wrapper">1 + (<input type="text" id="iAreaCode" name="area_code" maxlength="3" />) 555 5555</span>
			</fieldset>
		</div>
		<p>Buying a phone number will charge your Twilio account.  See <a href="http://www.twilio.com/pricing-signup" target="_blank">Twilio.com</a> for pricing information.</p>
	</form>

	<div id="completed-order" class="hide">
		<p>Here's your new number</p>
		<p class="number"></p>
		<a href="" class="setup link-button">Setup Flow</a>
		<br class="clear" />
		<p><a href="<?php echo site_url('numbers') ?>" class="skip-link">Setup later</a></p>
	</div>
</div>
