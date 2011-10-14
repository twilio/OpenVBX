<select name="browserphone_call_using" id="client-mode-status">
<?php
	foreach ($browserphone['call_using_options'] as $key => $value)
	{
		echo '<option value="'.$key.'"';
		if ($key == $browserphone['call_using'])
		{
			echo ' selected="selected"';
		}
		echo " data-device='".json_encode($value['data'])."'".
			'>'.$value['title'].'</option>';
	}
?>
</select>