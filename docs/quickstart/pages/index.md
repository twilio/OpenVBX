---
layout: docs
category: docs
---

**Making A Page**

#Getting Started#

###Overview###

In this quickstart we're going to go over the basics of setting up a page. Pages in OpenVBX can be built like normal PHP scripts but they have access to other elements like information stored by the plugins. You can find out more about this by checking out the <a href="{{ site.baseurl }}/docs/api/2010-06-01/plugin/data/plugin-data/">PluginData</a>.

###Getting Started###

The first step in building your page is to create a new folder in your plugins directory. You can reuse the directories that you make by adding other files to it.

{% highlight sh lineanchors tabsize=4 %}
mkdir OpenVBX/plugins/call-log
{% endhighlight %}

Now lets make the actual page file.

{% highlight sh lineanchors tabsize=4 %}
touch call_log.php
{% endhighlight %}
	
We also need to define a `plugin.json` file. This defines the page&rsquo;s name, who made it and has a brief description about the page. The links element defines the navigation for your page, this navigation will appear on the side of most elements. 

{% highlight json  lineanchors tabsize=4 %}
{% include code/quickstart/pages/plugin.json %} 
{% endhighlight %}

<a href="{{ site.baseurl }}/docs/quickstart/" class="prev-page"><span></span>Quickstart Home</a>
<a href="pages-1/" class="next-page"><span></span>Call Log v1.0</a>
<br class="clear" />
