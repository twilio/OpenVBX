---
layout: docs
category: docs
---

**Making An Applet**

#Hello Monkey v1.3#

To finish up our applet we&rsquo;re going to implement the functionality to add multiple phone numbers to check against and have different actions for each phone number. This is very similar to the menu applet where you can have as many or as few options as you would like. 

In order to achieve this, we first need to add a new element to our table in the `ui.php` file. This element is where the add and remove buttons will go. Next we need to modify the row in the table body, we&rsquo;ll add a foreach loop that will add the neccessarry table rows with the phone numbers that will be screened as well as the actions to be taken if the caller matches. Also a table footer has been added, this contains a dummy row in it that has blank information. Using javascript we&rsquo;ll use this dummy row to create new rows in the body as necessary. As you can also see we&rsquo;ve changed the names of the inputs and the dropzones to arrays. We&rsquo;ll use the phone numbers entered in as keys, and the dropzone values have been changed to choices.

{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.3/ui.php %}
{% endhighlight %}

{% highlight js  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.3/script.js %}
{% endhighlight %}

Finally we need to expand our TwiML to work with our new UI elements. We&rsquo;re going to create an associative array using the phone number that will be screened as the key, and the value of the dropzone will be the value. We can easily create an associative array using this function, `AppletInstance::assocKeyValueCombine()`. Now when we need to check what we should do with the caller we can simply use the phone number as the key and see if there is an applet we need to direct the caller to, if there isn&rsquo;t the fallback option will be used.

{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/applets/hellomonkey-1.3/twiml.php %}
{% endhighlight %}

Now when someone calls and this applet is used it will greet them with your custom message and check if the number has a custom option for it, if not the fallback option will be used.

If you&rsquo;re interested in expanding your Hello Monkey applet besure to check out the <a href="{{ site.baseurl }}/docs/api/2010-06-01/plugin/applets/applet-ui/">Applet API page</a>.

<a href="../applets-3/" class="prev-page"><span></span>Hello Monkey v1.2</a>
<br class="clear" />
