<?php
$message_type = $message->type == 'sms'? 'SMS' : 'Voicemail';
$content = "$message_type from {$message->caller}\n\n";
if(!empty($message->content_text))
{
	$label = $message->type == 'sms'? 'Message' : 'Transcription';
	$content .= "$label:\n\n" . $message->content_text . "\n";
}

$content .= "\n\n";

if($message->type == 'voice')
{
	$content .= "\n-----------------------------------\n\n";
	$content .= "Voicemail recorded {$message->created}\n\n";
	$content .= "Length of voicemail: {$message->size} seconds\n\n";
}

$content .= "\n\n";

$content .= "-----------------------------------\n\n";
$content .= "Link to details: ". site_url('/external/messages/details/'.$message->id). "\n";
if($message->type == 'voice')
{
	$content .= "Link to audio: {$message->content_url}.mp3\n";
}
?>

You have been assigned a message in the <?php echo $message->owner ?> group
<?php echo $content ?>
<?php if(!empty($annotations) && $message->type != 'sms'): ?>

<?php foreach($annotations as $annotation): if($annotation->type == 'noted'): ?>

<?php echo date('D, M j h:i', strtotime($annotation->created))."\n".$annotation->description; ?>

<?php endif; endforeach; ?>
<?php endif; ?>
