---
layout: docs
category: docs
---

**Browser Phone &raquo; Making &amp; Receiving Calls**

<!-- warning! bad grammar ahead! -->

# Making &amp; Receiving Calls with OpenVBX #

<img id="browserphone-docs-fl" src="{{ site.baseurl }}/images/mic-shadow.png" alt="Browser Phone" />The OpenVBX Browser Phone is an integration of [Twilio Client](http://twilio.com/api/client) in to the OpenVBX framework. Using the Browser Phone you can now make and accept calls directly within your browser when logged in to OpenVBX. When you install or upgrade OpenVBX your install will be automatically configured to use Twilio Client.


## Making Calls ##

Making calls is easy. Click on the _Call_ button in the toolbar. In the dialog box that appears enter the phone number you'd like to call in to the _Dial_ field and then click on the _Call_ button. The Browser Phone will appear and then dial.

While engaged in a call you can browse through your OpenVBX install like you normally would. The only limitation is that the browser window must stay open for the call to remain connected. This also means that you cannot use the browser's _Refresh_ button while on a call. For added safety you will receive a prompt from the browser if you try to close the window while on a call.

If instead you'd like to make a call using your primary device you can change the _Using_ selection in the _Dial_ dialog box before making the call. When set to the primary device you will first receive a phone call asking you to accept the call and then your outbound dial will be initiated. 


## Receiving Calls ##

Receiving calls requires a little set-up. To receive calls you must be set as the recipient of, or be part of a group that is the recipient of, a Dial Applet in a flow. When building your flow use the _User/Group_ picker in a Dial Applet to select your account, or a group that you are a part of, as the destination for the Dial action.

Once you are part of a Flow that will dial you you then go online using the _Online_ button located at the far right of the screen, just below the Logout button.


## <img align="right" src="{{ site.baseurl }}/images/online-screenshot.png" alt="Online!" />Online vs. Offline ##

The Online and Offline statuses are used for incoming calls only. When you are Online a Twilio Client device is automatically added as the first item to your devices list so that when a call is made to you the browser will be called as your primary device. There is no need to go Offline before logging out of OpenVBX. If you go offline the first active device in your devices list will become your primary device.

You can make outgoing calls when either status is selected. 