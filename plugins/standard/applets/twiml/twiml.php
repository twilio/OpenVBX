<?php
$next = AppletInstance::getDropZoneUrl('next');
echo '<?xml version="1.0" ?>';
echo '<Response>';
echo AppletInstance::getValue('twiml');
if(!empty($next))
{
	echo "<Redirect>".$next."</Redirect>";
}
echo '</Response>';
