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
					<script type="text/javascript">
					function upgradeNumber (phoneId) {
						$('#dlg_upgrade #phone_upgrade_id').val(phoneId);
						$('#dlg_upgrade').dialog('open');
					}
					</script>
					<?php foreach($items as $item): ?>
					<?php if (!$item['phone'] || !$item['raw_phone']) continue; ?>
					<tr rel="<?php echo $item['id'] ?>" class="items-row <?php if(in_array($item['id'], $highlighted_numbers)): ?>highlight-row<?php endif;?> <?php echo ($item['sandbox'])? 'sandbox-row' :'' ?>">
						<td class="incoming-number-phone"><?php echo ($item['sandbox'])? ($item['api_type'] == 'twilio') ? '<span class="sandbox-label">SANDBOX</span>' : '<span class="sandbox-label">DEVELOPMENT <a href="javascript:upgradeNumber(\''.$item['id'].'\');" class="upgrade-number" title="Upgrade to production"><img src="'.base_url().'/assets/i/up-icon.png" height="16" width="16" /></a></span>' : ''?><span class="numberinfo"><?php echo $item['phone'] ?> <?php echo !empty($item['pin'])? ' Pin: '.implode('-', str_split($item['pin'], 4)) : '' ?></span></td>
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
							<?php if ($item['api_type'] == 'twilio'): ?>
							<a href="numbers/delete/<?php echo $item['id']; ?>" class="action trash delete"><span class="replace">Delete</span></a>
							<?php else: ?>
							<a href="numbers/delete/<?php echo $item['id']; ?>/<?php echo $item['raw_phone']; ?>" class="action trash delete"><span class="replace">Delete</span></a>
							<?php endif; ?>
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

<?php // TODO: Fix the upgrade dialog site.css is weird ?>
<div id="dlg_upgrade" title="Upgrade application to production?" class="dialog">
	<p class="hide error-message"></p>
	
	<form action="<?php echo site_url('numbers/upgrade'); ?>" method="post">
		<p>Are you sure you want to upgrade your application to production?</p>
		<p>This will disable development mode for this specific Tropo application, and all sibling phone numbers, charging you from now on. See Tropo's pricing for details.</p>
		<input type="hidden" id="phone_upgrade_id" name="phone_upgrade_id" value="" />
	</form>
</div>

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
		<?php if (has_tropo() && has_twilio()): ?>
		<p>
			<label for="api_type">Order for</label>
			<select name="api_type">
				<option value="twilio">Twilio</option>
				<option value="tropo">Tropo</option>
			</select>
		</p>
		<?php elseif (has_tropo()): ?>
		<input type="hidden" name="api_type" value="tropo" />
		<?php elseif (has_twilio()): ?>
		<input type="hidden" name="api_type" value="twilio" />
		<?php endif; ?>
		<input type="radio" id="iTypeLocal" name="type" value="local" checked="checked" />
		<label for="iTypeLocal" class="field-label-inline">Local</label>
		<input type="radio" id="iTypeTollFree" name="type" value="tollfree" />
		<label for="iTypeTollFree" class="field-label-inline">Toll-Free</label>

		<div id="pAreaCode" class="area-code">
			<fieldset class="vbx-input-complex vbx-input-container">
				<label for="iAreaCode" class="area-code-label">Area Code</label>
				<span id="area-code-wrapper">
					<span id="country-code">
						<?php if (has_tropo() && !has_twilio()): ?>
						<input type="text" name="country_code" id="iCountryCode" maxlength="3" value="1" />
						<?php else: ?>
						1
						<?php endif; ?>
					</span>
					 + (<input type="text" id="iAreaCode" name="area_code" maxlength="3" />) 555 5555
				</span>
			</fieldset>
		</div>

		<?php if (has_tropo() && !has_twilio()): ?>
		<?php $tropoDisplay = ""; ?>
		<?php else: ?>
		<?php $tropoDisplay ="display:none"; ?>
		<div id="twilio-api-type">
			<p>Buying a phone number will charge your Twilio account.  See <a href="http://www.twilio.com/pricing-signup" target="_blank">Twilio.com</a> for pricing information.</p>
		</div>
		<?php endif; ?>

		<div id="tropo-api-type" style="<?php echo $tropoDisplay; ?>">
			<p>
				<label for="number_sibling">*Number Sibling</label>
				<select name="number_sibling">
					<option value="">-- No sibling --</option>
					<?php
					$uniqueAppIds = array();
					foreach ($items as $item) {
						if ($item['api_type'] == 'tropo' && 
						!in_array($item['id'], $uniqueAppIds)) {
							$uniqueAppIds[] = $item['id'];
							?><option value="<?php echo $item['id']; ?>"><?php echo $item['phone']; ?></option>
					<?php
						}
					}
					?>
				</select>
			</p>

			<p>Buying a phone number will charge your Tropo account.  See <a href="https://www.tropo.com/pricing/" target="_blank">Tropo.com</a> for pricing information.</p>

			<p class="descrition">*Number siblings are a Tropo unique feature. Numbers may be defined as "siblings", which will define the number under the same Tropo application as the siblings. This means the siblings will all use the same call flow. You can use this for different area codes that use the same call flow.</p>
		</div>
	</form>

	<div id="completed-order" class="hide">
		<p>Here's your new number</p>
		<p class="number"></p>
		<a href="" class="setup link-button">Setup Flow</a>
		<br class="clear" />
		<p><a href="<?php echo site_url('numbers') ?>" class="skip-link">Setup later</a></p>
	</div>
</div>
