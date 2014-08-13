<?php
echo '<?xml version="1.0" ?>';
echo '<Response>';
echo AppletInstance::getValue('twiml');
if ($next = AppletInstance::getDropZoneUrl('next')) {
 echo "<Redirect>".$next."</Redirect>";
}
echo '</Response>';
