<?php
	$ci =& get_instance();
	$ci->load->model('vbx_incoming_numbers');
	$ci->load->model('vbx_outgoing_caller_ids');
	$ci->load->helper('token_helper');

	try {
		$numbers = $ci->vbx_incoming_numbers->get_numbers();

	}
	catch (VBX_IncomingNumberException $e) {
		log_message('Incoming numbers exception: '.$e->getMessage().' :: '.$e->getCode());
		$numbers = array();
	}

	try {
		$outgoingCallerIds = $ci->vbx_outgoing_caller_ids->get_caller_ids();
	}
	catch (VBX_OutgoingCallerIdException $e) {
		log_message('Outgoing callerids exception: '.$e->getMessage().' :: '.$e->getCode());
		$outgoingCallerIds = array();
	}

	$number_options = array(
		'' => "Caller's Number",
		'called' => 'Called Number',
	);

	foreach($numbers as $number) {
		$number_options['Incoming Numbers'][$number->phone_number] = $number->phone . ' <' . $number->name . '>';
	}
	foreach($outgoingCallerIds as $outgoingCallerId) {
		$number_options['Verified Numbers'][$outgoingCallerId->phone_number] = $outgoingCallerId->phone . ' <' . $outgoingCallerId->name . '>';
	}

	$from_number = AppletInstance::getValue('from-number', null);
	$message_whom_selector = AppletInstance::getValue('message-whom-selector', 'caller');

?>
<div class="vbx-applet">
	<?php if(AppletInstance::getFlowType() == 'voice'): ?>
		<h3>Send a text message to the caller if they're on a mobile phone.</h3>
	<?php else: ?>
		<h3>Send a text message to the sender.</h3>
	<?php endif; ?>

	<br />
	<h2>Message Whom</h2>
	<div class="radio-table">
		<table>
			<tr class="radio-table-row first <?php echo ($message_whom_selector === 'caller') ? 'on' : 'off' ?>">
				<td class="radio-cell">
					<input type="radio" class='message-whom-selector-radio' name="message-whom-selector" value="caller" <?php echo ($message_whom_selector === 'caller') ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell">
					<h4>Message Caller</h4>
				</td>
			</tr>
			<tr class="radio-table-row <?php echo ($message_whom_selector === 'user-or-group') ? 'on' : 'off' ?>">
				<td class="radio-cell">
					<input type="radio" class='message-whom-selector-radio' name="message-whom-selector" value="user-or-group" <?php echo ($message_whom_selector === 'user-or-group') ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell">
					<h4>Message a user or group</h4>
					<?php echo AppletUI::UserGroupPicker('message-whom-user-or-group'); ?>
				</td>
			</tr>
			<tr class="radio-table-row last <?php echo ($message_whom_selector === 'number') ? 'on' : 'off' ?>">
				<td class="radio-cell">
					<input type="radio" class='message-whom-selector-radio' name="message-whom-selector" value="number" <?php echo ($message_whom_selector === 'number') ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell">
					<h4>Message phone number</h4>
					<div class="vbx-input-container input">
						<input type="text" class="medium" name="message-whom-number" value="<?php echo AppletInstance::getValue('message-whom-number') ?>"/>
					</div>
				</td>
			</tr>
		</table>
	</div>

	<br />
	<h2>From Number</h2>
	<div class="vbx-full-pane">
		<fieldset class="vbx-input-container">
			<?php
				$params = array(
					'name' => 'from-number',
					'class' => 'medium',
				);
				echo t_form_dropdown($params, $number_options, $from_number);
			?>
		</fieldset>
	</div>

	<br />
	<h2>Message</h2>
	<fieldset class="vbx-input-container">
		<textarea name="sms" class="medium"><?php echo AppletInstance::getValue('sms'); ?></textarea>
		<p>Text can include any of the following tokens:
        <?php echo token_list(); ?>
	</fieldset>
	<h2>Next</h2>
	<p>After the message is sent, continue to the next applet</p>
	<div class="vbx-full-pane">
		<?php echo AppletUI::DropZone('next'); ?>
	</div><!-- .vbx-full-pane -->

</div><!-- .vbx-applet -->
