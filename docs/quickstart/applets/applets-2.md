---
layout: docs
category: docs
---

**Making An Applet**

#Hello Monkey, v1.1#

Now that we&rsquo;ve got the basic applet built, lets make it a little bit more dynamic. The first thing that we&rsquo;re going to do is create a text box that will allow the person who adds the applet to put in their own custom message. Now we could use another UI Widget for this, but we&rsquo;ll use a text input instead to demonstrate how form elements can be used and styled within your `ui.php` file.

You may have noticed previously that we&rsquo;re surrounding `ui.php` with a div called &ldquo;vbx-applet&rdquo;. This class allows elements inside the div to inherit styles that make the applet look good. One of the advantages of using this class is that we can add a class to our textarea called `medium` and have it automatically setup the width and height of the textarea. For textareas there are 3 styles you can use: `small`, `medium` and `large`. In this case we&rsquo;ll go with `medium`.

{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.1/ui.php %}
{% endhighlight %}

Now we need to move onto our TwiML. Since we&rsquo;ve added a textarea to the UI we need to add something to get the value of the textarea in the TwiML. We can do this by using `AppletInstance::getValue()`. `AppletInstance::getValue()` can be used to get the value of any custom element you create, all you need to do is put the name of the element in and it will pull out the value. In this case we&rsquo;re going to add the text from the textarea into a custom say.

{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.1/twiml.php %}
{% endhighlight %}

If you&rsquo;d like to add an icon for your applet to match the other applets you can create an image file that is 24px &times; 24px and save it as `icon.png`. You should place the image file at `OpenVBX/plugins/hello-monkey/applets/monkey/`. Now when you go to your applet your icon should appear.

You can use this icon of a monkey we made for the Hello Monkey applet. <img src="{{ site.baseurl }}/images/monkey-icon.png" />
		

<a href="../applets-1/" class="prev-page"><span></span> Hello Monkey v1.0</a>
<a href="../applets-3/" class="next-page"><span></span> Hello Monkey v1.2</a>
<br class="clear" />

