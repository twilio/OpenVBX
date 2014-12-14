<div class="vbx-content-main">

		<div class="vbx-content-menu vbx-content-menu-top">
			<h2 class="vbx-content-heading">Devices</h2>
			<ul class="vbx-menu-items-right <?php if(empty($devices)): ?>hide<?php endif; ?>">
				<li class="menu-item"><button class="add-device add-button"><span>Add Device</span></button></li>
			</ul>
		</div><!-- .vbx-content-menu -->


		<div class="devices-blank <?php if(!empty($devices)): ?>hide<?php endif; ?>">
			<h2>Hey, you don't have any phones setup!</h2>
			<p>If you add devices, like your cell phone or landline, people can reach you on the phone.</p>
			<button class="add-device add-button"><span>Add Device</span></button>
		</div>


	<div class="vbx-content-container">

		<div class="vbx-content-section">
		<form class="vbx-form" action="">

		<div class="device-container">
		<h3>Devices</h3>
		<p>Ex: your cell phone, your home phone, your office phone, etc.</p>
		<ol class="device-list <?php if(empty($devices)): ?>hide<?php endif; ?>">
			<?php foreach($devices as $device): 
				if ($device->id == 0) { continue; } ?>
			<li class="phone device enabled ui-state-default" rel="<?php echo $device->id ?>">
				<fieldset class="vbx-input-complex">

					<label class="field-label-inline left">
						<div class="device-type phone-type"><span class="replace">Phone</span></div>
						<p class="device-name"><?php echo htmlentities($device->name); ?></p>
					</label>

					<p class="device-value"><?php echo format_phone($device->value); ?></p>

					<label class="field-label-inline left">
						<input type="checkbox" class="enable-sms checkbox" <?php echo $device->sms == 1? 'checked="checked"'  :'' ?> />
						SMS notifications
					</label>

					<a href="" class="action trash" title="Delete"><span class="replace">Delete</span></a>

					<div class="device-status">
						<a href="" class="<?php echo ($device->is_active == 1)? 'enabled' : 'disabled' ?> on">ON</a>
						<a href="" class="<?php echo ($device->is_active == 0)? 'enabled' : 'disabled' ?> off">OFF</a>
						<input type="checkbox" class="enable-device checkbox" value="1" <?php echo ($device->is_active == 1)? 'checked="checked"' : '' ?> />
					</div>

				</fieldset>
			</li>
			<?php endforeach; ?>

			<li class="prototype hide">
				<fieldset class="vbx-input-complex">
					<label class="field-label-inline left">
						<div class="device-type phone-type"><span class="replace">Phone</span></div>
						<p class="device-name"></p>
					</label>

					<p class="device-value"></p>

					<label class="field-label-inline left">
						<input type="checkbox" class="enable-sms checkbox" checked="checked"/>
						SMS notifications
					</label>

					<a href="" class="action trash" title="Delete"><span class="replace">Delete</span></a>
					<!-- <a href="" class="action edit" title="Edit"><span class="replace">Edit</span></a> -->

					<div class="device-status">
						<a href="" class="enabled on">ON</a>
						<a href="" class="disabled off">OFF</a>
						<input type="checkbox" class="enable-device checkbox" value="1" checked="checked" />
					</div>

				</fieldset>
			</li>
		</ol>

		<div class="no-devices <?php if(!empty($devices)): ?>hide<?php endif; ?>">
			<p>No devices setup.</p>
		</div><!-- .no-devices -->

		</div><!-- .device-container -->
		</form>

		<?php if($show_iphone_app): ?>
		<div class="application-container">
			<div class="application">
				<img class="app-icon" src="<?php echo ASSET_ROOT ?>/i/iphone-icon-58.png" alt="OpenVBX for iPhone" />
				<h4 class="app-name">OpenVBX for iPhone</h4>
				<p class="app-desc">Get the OpenVBX iPhone app and be
				able to access your voicemail, make calls and send text messages on the go. </p>
				<form>
					<fieldset class="vbx-input-container">
						<button class="email-button"><span>Start the Quick Install</span></button>
					</fieldset>
				</form>
			</div>
		</div>
		<?php endif;?>

		</div><!-- .vbx-content-section -->


	</div><!-- .vbx-content-container -->


</div><!-- .vbx-content-main -->



<div id="dialog-number" style="display: none;" class="new number dialog" title="Add Devices">
		<div class="hide error-message"></div>
		<div class="vbx-form">
			<fieldset class="vbx-input-container">
				<label class="field-label">Device Name
					<input type="text" class="medium" name="number[name]" value="" />
				</label>
			</fieldset>

			<fieldset class="vbx-input-container">
				<label class="field-label">Phone Number
					<input type="text" class="medium" name="number[value]" value="" />
				</label>
			</fieldset>
		</div>
</div>

<div id="dialog-email" style="display: none;" class="email dialog" title="OpenVBX for iPhone Install">
	<p>We've emailed the quick install guide to <?php echo $user->email ?>.  Open the email on your iPhone to begin.</p>
</div>


