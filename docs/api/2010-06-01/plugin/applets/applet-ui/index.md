---
layout: docs
category: docs
---

**Plugin API &raquo; Applets**

# Applet UI #

Use these methods to chain applets together, select users or groups, or even let a user pick whether to say or play a voice dialog.

### AppletUI::dropZone( _$name_ ) ###

To chain applets together, create dropZones in your applet user interfaces.  The name is a unique way of identifying different dropZones.

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
        <td>Name of the dropzone to be stored as, this will be the same to retrieve value with AppletInstance::getDropZoneURL()</td>
    </tr>
</tbody>
</table>

#### Returns ####

string &mdash; html of _dropZone_

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
echo AppletUI::dropZone('next');
{% endhighlight %}

#### Output ####

![Drop Zone]({{ site.baseurl }}/images/docs/dropZone.png "Drop Zone")


### AppletUI::audioSpeechPicker( _$name_ ) ###
If you want to allow a user to upload their own mp3s, record a message via their phone or use the text-to-speech engine, place this in your applet interface.  

#### Arguments ####

 * $name &mdash; name of audioSpeechPicker

#### Returns ####

string &mdash; html of _audioSpeechPicker_

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
echo AppletUI::audioSpeechPicker('audioSpeechPicker');
{% endhighlight %}

#### Output ####

![Audio Speech Picker]({{ site.baseurl }}/images/docs/audioSpeechPicker.png "Audio Speech Picker")


### AppletUI::userGroupPicker( _$name = 'userGroupPicker', $label = 'Select a User or Group'_ ) ###

If your applet needs to select a user or group of users, use this picker.

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
        <td>Name of userGroupPicker</td>
    </tr>
    <tr>
        <td>$label</td>
        <td>string</td>
        <td>Helper text displayed in the clickable region of the picker</td>
    </tr>
</tbody>
</table>

#### Returns ####

string &mdash; html of _userGroupPicker_

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
echo AppletUI::userGroupPicker('userGroupPicker');
{% endhighlight %}

#### Output ####

![User Group Picker]({{ site.baseurl }}/images/docs/userGroupPicker.png "User Group Picker")
