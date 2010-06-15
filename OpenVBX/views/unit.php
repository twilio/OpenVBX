<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<h2>Unit tests for <?php echo $title ?></h2>
<table class="unit-tests">
	<?php foreach($result as $test): ?>
	<tr class="unit-title">
		<td><h2 class="test-result <?php echo strtolower($test['Result']) ?>"><?php echo $test['Result'] ?></h2></td>
		<td>
			<h2 class="test-name"><?php echo $test['Test Name'] ?></h2>
			<div class="details">
				<span class="test-file-name"><?php echo $test['File Name'] ?></span>::<span class="test-line-number"><?php echo $test['Line Number'] ?></span>
				
				<p>Expected: <span class="test-expected-datatype"><?php echo $test['Expected Datatype'] ?></span></p>
				<p>Returned: <span class="test-datatype"><?php echo $test['Test Datatype'] ?></span></p>
			</div>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
