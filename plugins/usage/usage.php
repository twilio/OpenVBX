<?php
// get usage
$account = OpenVBX::getAccount();
$account_sid = $account->sid;
$usage_data = $account->usage_records->this_month;
$shortcodeTotal = 0;
foreach ($usage_data as $record) {
	if (!isset($usage_start)) {
		$usage_start = $record->start_date;
		$usage_end = $record->end_date;
	}

	$category = $record->category;
	$price = $record->price;
	if (strpos('shortcode', $category) !== false) {
		$shortcodeTotal += $price;
	}

	$usage[$category] = array(
		'price' => !empty($price) ? $price : 0,
		'description' => $record->description,
		'price_unit' => $record->price_unit,
		'count' => $record->count,
		'count_unit' => $record->count_unit,
	);
}

// usage is always in USD as of the time of writing this
$usage_denominator = '$';
?>
<div class="vbx-plugin usage-vbx-plugin">
    <div id="usage">
        <h2>Usage</h2>

		<br />

		<p>Usage data for account <?php echo $account_sid; ?>, <?php echo $usage_start; ?> through <?php echo $usage_end; ?></p>

		<br />

        <h3>SMS Messages</h3>

        <table class="vbx-items-grid">
            <thead>
				<tr class="items-head">
					<th colspan="2">Item</th>
					<th>Count</th>
					<th>Cost</th>
				</tr>
            </thead>
            <tbody>
				<tr class="items-row">
					<td>SMS Inbound</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['sms-inbound']['count'] . ' ' .$usage['sms']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['sms-inbound']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>SMS Outbound</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['sms-outbound']['count'] . ' ' .$usage['sms']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['sms-outbound']['price']; ?></td>
				</tr>
<?php if ($shortcodeTotal > 0) { ?>
				<tr class="items-row">
					<td>SMS Inbound Shortcode</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['sms-inbound-shortcode']['count'] . ' ' .$usage['sms']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['sms-inbound-shortcode']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>SMS Outbound Shortcode</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['sms-outbound-shortcode']['count'] . ' ' .$usage['sms']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['sms-outbound-shortcode']['price']; ?></td>
				</tr>
<?php } /* close shortcodes */ ?>
				<tr class="items-row">
					<td>&nbsp;</td>
					<td class="usage-group-total">SMS Total</td>
					<td><?php echo $usage['sms']['count']  . ' ' .$usage['sms']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['sms']['price']; ?></td>
				</tr>
            </tbody>
        </table>

        <h3>Phone Calls</h3>

        <table class="vbx-items-grid">
            <thead>
				<tr class="items-head">
					<th colspan="2">Item</th>
					<th>Count</th>
					<th>Cost</th>
				</tr>
            </thead>
            <tbody>
				<tr class="items-row">
					<td>Calls Inbound</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['calls-inbound']['count']  . ' ' .$usage['calls']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['calls-inbound']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>Calls Outbound</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['calls-outbound']['count'] . ' ' .$usage['calls']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['calls-outbound']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>Calls Inbound Toll Free</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['calls-inbound-tollfree']['count']  . ' ' .$usage['calls']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['calls-inbound-tollfree']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>Client</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['calls-client']['count'] . ' ' .$usage['calls']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['calls-client']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>&nbsp;</td>
					<td class="usage-group-total">Calls Total</td>
					<td><?php echo $usage['calls']['count'] . ' ' .$usage['calls']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['calls']['price']; ?></td>
				</tr>
            </tbody>
        </table>

<?php if ($shortcodeTotal > 0) { ?>
        <h3>Shortcodes</h3>

        <table class="vbx-items-grid">
            <thead>
				<tr class="items-head">
					<th colspan="2">Item</th>
					<th>Count</th>
					<th>Cost</th>
				</tr>
            </thead>
            <tbody>
				<tr class="items-row">
					<td>Vanity Shortcodes</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['shortcodes-vanity']['count'] . ' ' .$usage['shortcodes']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['shortcodes-vanity']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>Random Shortcodes</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['shortcodes-random']['count'] . ' ' .$usage['shortcodes']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['shortcodes-random']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>Customer Owned Shortcodes</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['shortcodes-customerowned']['count'] . ' ' .$usage['shortcodes']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['shortcodes-customerowned']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>&nbsp;</td>
					<td class="usage-group-total">Shortcodes Total</td>
					<td><?php echo $usage['shortcodes']['count'] . ' ' .$usage['shortcodes']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['shortcodes']['price']; ?></td>
				</tr>
            </tbody>
        </table>
<?php } /* close shortcodes */ ?>

        <h3>Misc.</h3>

        <table class="vbx-items-grid">
            <thead>
				<tr class="items-head">
					<th colspan="2">Item</th>
					<th>Count</th>
					<th>Cost</th>
				</tr>
            </thead>
            <tbody>
				<tr class="items-row">
					<td>Recordings</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['recordings']['count'] . ' ' .$usage['recordings']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['recordings']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>Recording Storage</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['recordingstorage']['count'] . ' ' .$usage['recordingstorage']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['recordingstorage']['price']; ?></td>
				</tr>
				<tr class="items-row">
					<td>Transcriptions</td>
					<td>&nbsp;</td>
					<td><?php echo $usage['transcriptions']['count'] . ' ' .$usage['transcriptions']['count_unit']; ?></td>
					<td><?php echo $usage_denominator . $usage['transcriptions']['price']; ?></td>
				</tr>
            </tbody>
        </table>
	</div>
</div>