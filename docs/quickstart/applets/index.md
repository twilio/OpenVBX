---
layout: docs
category: docs
---

**Making An Applet**

#Getting Started#

###Overview###

Out of the box OpenVBX comes with a set of applets like: dial, hangup, greeting, voicemail, etc. While you can string them together and create some very impressive call flows sometimes you need some custom functionality that you just can&rsquo;t get out of the pre-existing applets.

Luckily you can write your own applets to extend your version of OpenVBX. This quickstart begins by showing you how to make a simple applet with a prompt and ends with you building a fully functional call screening applet.

If you would like to use the existing applets as reference they can be found in your OpenVBX folder under `/plugins/standard/applets`

###Getting Started###

The first step to building your applet is to create a few empty files and directories inside your `OpenVBX/plugins/` directory.

You can reuse these files and directories for other applets. So First lets create a directory for the name of your applet inside of the `<openvbx>/plugin` directory.

{% highlight sh lineanchors tabsize=4 %}	
mkdir OpenVBX/plugins/hello-monkey
{% endhighlight %}

Now we define something about the applet: The applet name, who built it, etc. You define this through a file called `plugin.json` file.

{% highlight sh lineanchors tabsize=4 %}	
touch OpenVBX/plugins/hello-monkey/plugin.json
{% endhighlight %}

If you&rsquo;re familiar with json, then this should be a walk through the park, otherwise just copy and paste our example into your favorite text editor.

{% highlight json  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.0/plugin.json %}
{% endhighlight %}

Since HelloMonkey is an applet we need to add an applets directory.
	
{% highlight sh lineanchors tabsize=4 %}
mkdir -p OpenVBX/plugins/hello-monkey/applets/monkey
{% endhighlight %}

Now we&rsquo;ll create a few files to define the applet: the ui, the twiml and another json file.

{% highlight sh lineanchors tabsize=4 %}
touch OpenVBX/plugins/hello-monkey/applets/monkey/ui.php
touch OpenVBX/plugins/hello-monkey/applets/monkey/twiml.php
touch OpenVBX/plugins/hello-monkey/applets/monkey/applet.json
{% endhighlight %}

###Code###

You can download a the code used in this example if you would like:

- Tarball: [hellomonkey.tar.gz]({{ site.baseurl }}/resources/code/quickstart/applets/hellomonkey.tar.gz)
- ZIP: [hellomonkey.zip]({{ site.baseurl }}/resources/code/quickstart/applets/hellomonkey.zip)

<a href="{{ site.baseurl }}/docs/quickstart/" class="prev-page"><span></span> Quickstart Home</a>
<a href="applets-1/" class="next-page"><span></span> Hello Monkey v1.0</a>
<br class="clear" />
