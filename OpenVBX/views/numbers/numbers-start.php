<?php
	$class = 'numbers-blank';
	if($count_real_numbers > 0)
	{
		$class .= ' hide';
	}
?>
<div class="<?php echo $class; ?>">
	<h2>Hey, you don't have any of your own phone numbers!</h2>
	<p>You can get toll free numbers, or local numbers in nearly any area code, that people can use to call you.</p>
	<button class="add-button add number"><span>Get a Number</span></button>
</div>