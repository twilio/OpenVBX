---
layout: docs
category: docs
---

**Making An Applet**

#Hello Monkey, v1.0#

We&rsquo;re now ready to get started with making the Hello Monkey applet.

First, let&rsquo;s fill out the applet.json file. This file is used to identify what the name of the applet is, as well as provide a brief description. We&rsquo;ll also set they type of the applet to &ldquo;voice&rdquo;, which indicates this applet is used in a voice call flow. If you would like to build an SMS applet you can change the type to &ldquo;sms&rdquo;.  If you want your applet to work with both you can define type as an array, like this: `"type" : ["voice", "sms"]`

Edit `OpenVBX/plugins/hello-monkey/applets/monkey/applet.json` to look like:

{% highlight json  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.0/applet.json %}
{% endhighlight %}

The UI of your applet is defined by the html and php in the `ui.php` file. This applet will say &ldquo;Hello Monkey&rdquo; to the caller using the text to speech engine, so we need to describe this to the person who uses this applet in a call flow. We also want to add a DropZone so that the user can add another applet to execute, after this applet completes.

Edit `OpenVBX/plugins/hello-monkey/applets/monkey/ui.php` to look like: 

{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.0/ui.php %} 
{% endhighlight %}

Finally, lets fill out the `twiml.php` file. This describes the TwiML that executes on a call when it reaches this applet. 

Edit `OpenVBX/plugins/hello-monkey/applets/monkey/twiml.php` to look like:

{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.0/twiml.php %}
{% endhighlight %}

Now when you go to the Call Flow editor, you should see this applet in the sidebar. If you add this applet to a call flow, the caller will be greeted with &ldquo;Hello Monkey&rdquo; and will then be directed to the next applet.

<a href="../" class="prev-page"><span></span> Getting Started</a>
<a href="../applets-2/" class="next-page"><span></span> Hello Monkey v1.1</a>
<br class="clear" />
