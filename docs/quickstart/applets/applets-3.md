---
layout: docs
category: docs
---

**Making An Applet**

#Hello Monkey v1.2#

To finish up our Hello Monkey quickstart we&rsquo;re going to implement a call screening function that is going to check the incoming caller&rsquo;s phone number against a user or group of your choice. If the incoming number matches the user or group you&rsquo;ve selected, then the primary dropzone will be used to direct the call. If the caller is unknown then a secondary dropzone will be used to direct the call.

We&rsquo;ll begin by adding the `UserGroupPicker` element as well as a second dropzone.

{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.2/ui.php %}
{% endhighlight %}

Now that we&rsquo;ve got the UI setup we need to expand `twiml.php`.

{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.2/twiml.php %}
{% endhighlight %}

Now when someone calls and this applet is used it will greet them with your custom message and check if the number they&rsquo;re calling from matches the selected user/group. If so it will add the primary redirect and if the caller is unknown then the fallback redirect is added.

If you&rsquo;re interested in expanding your Hello Monkey applet be sure to check out the <a href="{{ site.baseurl }}/docs/api/2010-06-01/plugin/applets/applet-ui/">Applet API page</a>.

<a href="../applets-2/" class="prev-page"><span></span> Hello Monkey v1.1</a>
<a href="../applets-4/" class="next-page"><span></span> Hello Monkey v1.3</a>
<br class="clear" />
