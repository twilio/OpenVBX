<?php
$message_type = $message->type == 'sms'? 'SMS' : 'Voicemail';
echo "$message_type from {$message->caller}\n\n";
if(!empty($message->content_text))
{
	$label = $message->type == 'sms'? 'Message' : 'Transcription';
	echo "$label:\n\n" . $message->content_text . "";
}

echo "\n\n";

if($message->type == 'voice')
{
	echo "-----------------------------------\n\n";
	echo "Voicemail recorded {$message->created}\n\n";
	echo "Length of voicemail: {$message->size} seconds\n\n";
}

echo "-----------------------------------\n\n";
echo "Link to details: ". site_url('external/messages/details/'.$message->id). "\n";
if($message->type == 'voice')
{
	echo "Link to audio: {$message->content_url}.mp3\n";
}
?>
