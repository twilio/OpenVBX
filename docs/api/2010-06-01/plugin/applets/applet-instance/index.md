---
layout: docs
category: docs
---

**Plugin API &raquo; Applets**

# Applet Instance #

AppletInstance defines a bunch of methods to access the data stored in an Applet.
Using these methods, you can access the information stored by the flow editor in your applet coding(ui.php and twiml.php).

For more information on developing applets please see the <a href="{{ site.baseurl }}/docs/quickstart/applets/">Applet Quickstart</a>.

### AppletInstance::getValue( _$name, $default = ''_ ) ###

If you need to get a value you set inside your Applet UI from an input, select or checkbox, use getValue()

#### Arguments ####

<table class="parameters">
<thead>
	<tr>
		<th class="col-1">Name</th>
		<th class="col-2">Type</th>
		<th class="col-3">Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>$name</td>
		<td>string</td>
		<td>To select an array or an item on the array, use name[] or name[1]. They will return an array or value of the array.</td>
	</tr>
	<tr>
		<td>$default</td>
		<td>mixed</td>
	<td>Default value if no data is stored for that name.</td>
	</tr>
</tbody>
</table>

#### Returns ####

mixed

#### Usage ####
  
{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$value = AppletInstance::getValue('text-entry');
{% endhighlight %}

#### Examples ####
    
Accessing a value from a textbox in an Applet's user interface named _'monkey-name'_.

{% highlight html lineanchors tabsize=4 %}
<input type="text" name="monkey-name" value="" />
{% endhighlight %}

<br />
In your Applet ui.php or twiml.php:

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$name = AppletInstance::getValue('monkey-name', 'howie');
{% endhighlight %}

<br />    
In your Applet ui.php

{% highlight html lineanchors tabsize=4 %}
<input type="text" name="monkey-name" value="<?php echo AppletInstance::getValue('monkey-name', 'howie'); ?>" />
{% endhighlight %}

### AppletInstance::getDropZoneUrl( _$name = 'dropZone'_ ) ###

When you want to redirect a caller from a dropZone, use this method to get the url to redirect to. 

#### Arguments ####

<table class="parameters">
<thead>
	<tr>
		<th class="col-1">Name</th>
		<th class="col-2">Type</th>
		<th class="col-3">Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>$name</td>
		<td>string</td>
		<td>To select an array or an item on the array, use name[] or name[1]. They will return an array or value of the array.</td>
	</tr>
</tbody>
</table>

#### Returns ####
mixed &mdash; URL(s) to Applet Instance set to the dropzone

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$next = AppletInstance::getDropZoneUrl('next');
{% endhighlight %}

#### Examples ####

Redirecting to a dropzone in twiml.php

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$next = AppletInstance::getDropZoneUrl('next');
$response = new TwimlResponse;
$response->redirect($next);
$response->respond();
{% endhighlight %}

#### Output ####

{% highlight xml lineanchors tabsize=4 %}
<Response>
    <Redirect>http://example.com/twiml/applet/voice/1/f34dsa</Redirect>
</Response>
{% endhighlight %}

### AppletInstance::getDropZoneValue( _$name = 'dropZone'_ ) ###

When you just want to know what the applet instance path is inside your flow, use this method.  

#### Arguments ####

<table class="parameters">
<thead>
	<tr>
		<th class="col-1">Name</th>
		<th class="col-2">Type</th>
		<th class="col-3">Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>$name</td>
		<td>string</td>
		<td>To select an array or an item on the array, use name[] or name[1]. They will return an array or value of the array.</td>
	</tr>
</tbody>
</table>

#### Returns ####

mixed

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$value = AppletInstance::getDropZoneValue('text-entry');
{% endhighlight %}

#### Examples ####
Implementation of getDropZoneUrl using getDropZoneValue

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
function getDropZoneUrl($name = 'dropZone')
{
    $values = AppletInstance::getDropZoneValue($name);
    if(empty($values))
    {
        return '';
    }

    if(is_string($values))
    {
        $values = array($values);
    }

    /* Build drop zone urls from values */
    $urls = array();
    foreach($values as $i => $value)
    {
        if(empty($value))
        {
            $urls[$i] = '';
            continue;
        }
		
        $parts = explode('/', $value);
        $value = $parts[count($parts) - 1];
        
        $urls[$i] = join('/', array(
                        AppletInstance::$baseURI,
                        $value));
    }

    if(count($urls) > 1)
    {
        return $urls;
    }
    
    return !empty($urls)? $urls[0] : '';
}
{% endhighlight %}
  
#### Output ####

{% highlight html lineanchors tabsize=4 %}
http://example.com/OpenVBX/twiml/applet/voice/1/f34dsa
{% endhighlight %}

### AppletInstance::getAudioSpeechPickerValue( _$name = 'audioSpeechPickerValue'_ ) ###

When you want to get the selection of the audio or speech text a user filled out, use this method.  

Use the helper method, `AudioSpeechPickerWidget::getVerbForValue($pickerValue)` with this method to retrieve the say or play objects to add to your responses.

#### Arguments ####

<table class="parameters">
<thead>
	<tr>
		<th class="col-1">Name</th>
		<th class="col-2">Type</th>
		<th class="col-3">Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>$name</td>
		<td>string</td>
		<td>To select an array or an item on the array, use name[] or name[1]. They will return an array or value of the array.</td>
	</tr>
</tbody>
</table>

#### Returns ####

mixed &mdash; URL(s) or string(s) of text to be given to the speech engine.

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$value = AppletInstance::getAudioSpeechPickerValue('text-entry');
{% endhighlight %}

#### Examples ####

Presenting audioSpeechPicker value in twiml.php

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$response = new TwimlResponse;

$say_or_play = AppletInstance::getAudioSpeechPickerValue('say-or-play');
AudioSpeechPickerWidget::setVerbForValue($say_or_play, $response);

$response->respond();
{% endhighlight %}

### AppletInstance::getUserGroupPickerValue( _$name = 'userGroupPicker'_ ) ###

When you want to get a user or group selected by the user in your applets, use this method.  It will return either a `VBX_User` or `VBX_Group` object.

#### Arguments ####

<table class="parameters">
<thead>
	<tr>
		<th class="col-1">Name</th>
		<th class="col-2">Type</th>
		<th class="col-3">Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>$name</td>
		<td>string</td>
		<td>To select an array or an item on the array, use name[] or name[1]. They will return an array or value of the array.</td>
	</tr>
</tbody>
</table>

#### Returns ####

mixed &mdash; URL(s) or string(s) of text to be given to the speech engine.

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$value = AppletInstance::getUserGroupPickerValue('text-entry');
{% endhighlight %}

#### Examples ####
Getting devices and voicemail for a selected a user or group:

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$dial_whom_user_or_group = AppletInstance::getUserGroupPickerValue('dial-whom-user-or-group');
switch(get_class($dial_whom_user_or_group))
{
    case 'VBX_User':
        foreach($dial_whom_user_or_group->devices as $device)
        {
            $numbers[] = $device->value;
        }

        $voicemail = $dial_whom_user_or_group->voicemail;
        break;

    case 'VBX_Group':
        foreach($dial_whom_user_or_group->users as $user)
        {
            $user = VBX_User::get($user->user_id);
            foreach($user->devices as $device)
            {
                $numbers[] = $device->value;
            }
        }

        $voicemail = $no_answer_group_voicemail;
        break;

    default:
        break;
}
{% endhighlight %}

### AppletInstance::getInstanceId( ) ###

If you want to get the current Applet Instance unique id, use this method.  

#### Arguments ####

None

#### Returns ####

string &mdash; Instance ID

#### Examples ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$instance_id = AppletInstance::getInstanceId();
echo $instance_id;
{% endhighlight %}

#### Output ####

Usually six character long string like below:

    17d6bf


### AppletInstance::getFlowType( ) ###

When you need to detect whether an instance is either an "sms" or "voice" applet, call `AppletInstance::getFlowType()`.

#### Arguments ####

None

#### Returns ####

string &mdash; "sms" or "voice"

#### Examples ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$response = new TwimlResponse;

if(AppletInstance::getFlowType() == "sms")
{
    $response->sms('Reply with Sms');
}
else
{
    $response->say('I am a robot');
}
{% endhighlight %}
