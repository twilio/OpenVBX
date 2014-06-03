---
layout: docs
category: docs
---

**Browser Phone &raquo; API**

# Browser Phone API #

The Browser Phone API allows you to easily make new calls or manage with a few easy to use JavaScript methods.



### OpenVBX.clientDial( { 'to': '+14045551212', 'from': '+14155551212'}) ###
Pass To and From to this function and the dialer will initiate the call process and pop out on the screen.

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
        <td>to</td>
        <td>string</td>
        <td>The phone number of the person you're trying to call</td>
    </tr>
    <tr>
        <td>from</td>
        <td>string</td>
        <td>The phone number to use as the Caller ID for the call.  This must be a number you are allowed to use as a Caller ID with the Twilio API (i.e. a Twilio number of a validated caller ID).</td>
    </tr>
</tbody>
</table>

#### Returns ####
void &mdash; returns no data

#### Usage ####

{% highlight js lineanchors tabsize=4 %}
OpenVBX.clientDial({
  'to': '+17203089773',
  'callerid': '+14158774003'
});
{% endhighlight %}



### OpenVBX.clientHangup() ###

Disconnects the phone call and closes the dialer pad

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
		<td colspan="3">No Arguments</td>
	</tr>
</tbody>
</table>

#### Returns ####

Void &mdash; returns no data

#### Usage ####

{% highlight js lineanchors tabsize=4 %}
OpenVBX.clientHangup();
{% endhighlight %}


### OpenVBX.clientMute() ###

Mute the active call

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
		<td colspan="3">No Arguments</td>
	</tr>
</tbody>
</table>

#### Returns ####

Void &mdash; returns no data

#### Usage ####

{% highlight js lineanchors tabsize=4 %}
OpenVBX.clientMute();
{% endhighlight %}


### OpenVBX.clientUnMute() ###

Unmute the active call

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
		<td colspan="3">No Arguments</td>
	</tr>
</tbody>
</table>

#### Returns ####

Void &mdash; returns no data

#### Usage ####

{% highlight js lineanchors tabsize=4 %}
OpenVBX.clientUnMute();
{% endhighlight %}


### OpenVBX.clientIsReady() ###

Check that the Client has connected with Twilio and is ready to make a call

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
		<td colspan="3">No Arguments</td>
	</tr>
</tbody>
</table>

#### Returns ####

boolean

#### Usage ####

{% highlight js lineanchors tabsize=4 %}
OpenVBX.clientIsReady();
{% endhighlight %}
