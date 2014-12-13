<?php
	$ci =& get_instance();
	$ci->load->model('vbx_incoming_numbers');
	
	try {
		$numbers = $ci->vbx_incoming_numbers->get_numbers();
	}
	catch (VBX_IncomingNumberException $e) {
		log_message('Incoming numbers exception: '.$e->getMessage.' :: '.$e->getCode());
		$numbers = array();
	}
	
	$callerId = AppletInstance::getValue('callerId', null);
	$version = AppletInstance::getValue('version', null);

	if (AppletInstance::getValue('dial-whom-selector', 'user-or-group') === 'user-or-group')
	{
		$showVoicemailAction = true;
	}
	else
	{
		$showVoicemailAction = false;
	}
	
	$userOrGroup = AppletInstance::getUserGroupPickerValue('dial-whom-user-or-group');
	if ($userOrGroup instanceof VBX_Group)
	{
		$showGroupVoicemailPrompt = true;
	}
	else
	{
		$showGroupVoicemailPrompt = false;
	}

	$dial_whom_selector = AppletInstance::getValue('dial-whom-selector', 'user-or-group');
	$no_answer_action = AppletInstance::getValue('no-answer-action', 'voicemail');
    $whisper = AppletInstance::getValue('dial-whisper', true);

?>
<div class="vbx-applet dial-applet">

	<h2>Dial Whom</h2>
	<div class="radio-table">
		<table>
			<tr class="radio-table-row first <?php echo ($dial_whom_selector === 'user-or-group') ? 'on' : 'off' ?>">
				<td class="radio-cell">
					<input type="radio" class='dial-whom-selector-radio' name="dial-whom-selector" value="user-or-group" <?php echo ($dial_whom_selector === 'user-or-group') ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell">
					<h4>Dial a user or group</h4>
					<?php echo AppletUI::UserGroupPicker('dial-whom-user-or-group'); ?>
				</td>
			</tr>
			<tr class="radio-table-row last <?php echo ($dial_whom_selector === 'number') ? 'on' : 'off' ?>">
				<td class="radio-cell">
					<input type="radio" class='dial-whom-selector-radio' name="dial-whom-selector" value="number" <?php echo ($dial_whom_selector === 'number') ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell">
					<h4>Dial phone number</h4>
						<div class="vbx-input-container input">
							<input type="text" class="medium" name="dial-whom-number" value="<?php echo AppletInstance::getValue('dial-whom-number') ?>"/>
						</div>
				</td>
			</tr>
		</table>
	</div>

	<br />
	<h2>Caller ID</h2>
	<div class="vbx-full-pane">
		<fieldset class="vbx-input-container">
			<select class="medium" name="callerId">
				<option value="">Caller's Number</option>
<?php if(count($numbers)) foreach($numbers as $number): $number->phone = normalize_phone_to_E164($number->phone); ?>
				<option value="<?php echo $number->phone; ?>"<?php echo $number->phone == $callerId ? ' selected="selected" ' : ''; ?>><?php echo $number->name; ?></option>
<?php endforeach; ?>
			</select>
		</fieldset>
	</div>

    <br />
	<h2>Whisper</h2>
	<div class="radio-table">
		<table>
			<tr class="radio-table-row first <?php echo ($whisper) ? 'on' : 'off' ?>">
				<td class="radio-cell">
					<input type="radio" class='dial-whisper-radio' name="dial-whisper" value="1" <?php echo ($whisper) ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell">
					<h4>Announce the caller</h4>
				</td>
			</tr>
			<tr class="radio-table-row last <?php echo (!$whisper) ? 'on' : 'off' ?>">
				<td class="radio-cell">
					<input type="radio" class='dial-whisper-radio' name="dial-whisper" value="0" <?php echo (!$whisper) ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell">
					<h4>Connect without announcing</h4>
				</td>
			</tr>
		</table>
	</div>

	<br />
	<h2>If nobody answers...</h2>
	<div class="radio-table no-answer nobody-answers-user-group <?php echo ($dial_whom_selector === 'user-or-group')? '' : 'hide' ?>">
		<table>
			<tr class="voicemail-row radio-table-row first <?php echo ($no_answer_action === 'voicemail') ? 'on' : 'off' ?> <?php echo $showVoicemailAction ? '' : 'hide' ?>">
				<td class="radio-cell">
					<input type="radio" class='no-answer-action-radio' name="no-answer-action" value="voicemail" <?php echo ($no_answer_action === 'voicemail') ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell" style="vertical-align: middle;">
					<div class="personal-voicemail <?php echo $showGroupVoicemailPrompt ? 'hide' : '' ?>">
						<h4>Take a voicemail</h4>
					</div>
					<div class="group-voicemail <?php echo $showGroupVoicemailPrompt ? '' : 'hide' ?>">
						<table><tr style="border-bottom-width: 0px;">
							<td align="left" style="vertical-align: middle;"><h4>Take a voicemail</h4></td>
							<td>&nbsp;&nbsp;&nbsp;</td>
							<td style="width: 100%; vertical-align: middle; text-align: right;">
								<label><b>Personalized Greeting</b>
								<?php echo AppletUI::AudioSpeechPicker('no-answer-group-voicemail'); ?>
								</label>
							</td>
						</tr></table>
					</div>
				</td>
			</tr>
			<tr class="radio-table-row last <?php echo ($no_answer_action === 'redirect') ? 'on' : 'off' ?>">
				<td class="radio-cell">
					<input type="radio" class='no-answer-action-radio' name="no-answer-action" value="redirect" <?php echo ($no_answer_action === 'redirect') ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell" style="vertical-align: middle;">
					<table><tr style="border-bottom-width: 0px;">
						<td align="left" style="vertical-align: middle;"><h4>Go to</h4></td>
						<td align="right">
							<?php echo AppletUI::DropZone('no-answer-redirect') ?>
						</td>
					</tr></table>
				</td>
			</tr>
		</table>
	</div>
	<div class="vbx-full-pane nobody-answers-number <?php echo ($dial_whom_selector === 'number')? '' : 'hide' ?>">
		<?php echo AppletUI::DropZone('no-answer-redirect-number') ?>
	</div>
												
	<!-- Set the version of this applet -->
	<input type="hidden" name="version" value="3" />
</div><!-- .vbx-applet -->
