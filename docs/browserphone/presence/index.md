---
layout: docs
category: docs
---

**Browser Phone &raquo; Presence**

# Twilio Presence #

OpenVBX contains hooks for monitoring [Presence](http://www.twilio.com/docs/client/device#presence) events on Twilio Client.

Presence events fire each time a user connects, disconnects, or toggles their online status in their browser.



### OpenVBX.presence ###

Bind an event listener and callback function to `OpenVBX.presence` to receive Client Presence events on your page.

#### Parameters ####

Your callback function must take 2 parameters

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
        <td>client</td>
        <td>Object</td>
        <td>The current event object. This object will have 2 members:<ul><li>from: The client ID that triggered the event. In the case of OpenVBX this is the user's system ID in OpenVbX</li><li>available: a boolean value of `true` or `false`</li></ul></td>
    </tr>
    <tr>
        <td>onlineClients</td>
        <td>Array</td>
        <td>The current list of online client IDs.</td>
    </tr>
</tbody>
</table>

#### Returns ####
void 

#### Usage ####

{% highlight js lineanchors tabsize=4 %}
jQuery(function($) {
  $(OpenVBX.presence).bind('presence', function(client, onlineClients) {
    // ... your code here
  });
});
{% endhighlight %}
