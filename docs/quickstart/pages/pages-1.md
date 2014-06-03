---
layout: docs
category: docs
---

**Making A Page**

#Creating The Call Log#

The example page that we&rsquo;ll be creating for the quickstart is a call log for your OpenVBX instalation. With this page, you will be able to see what calls were made, when they were made, how long they lasted, and what the Status of the call is. This is both a helpful tool to have on your install of OpenVBX as well as a neat way to get started with creating pages.

The page starts by setting up a few local variables. First off an API Resource of your Twilio Account is assigned to `$account`. Next a limit is set to 50 to avoid returning every call in your history as that would get very time consuming as the list gets longer. Lastly the call is made to Twilio to get a page of the call history and assigns the resulting iterator to the `$calls` variable.

{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/pages/call_log_1.php %}
{% endhighlight %}

Next the page needs a few helper functions for the output. `humanize()` helps display computer friendly content in a human friendly manner. `number_text()` looks at the number given and determines wether its a phone number or a client id. If it is a client id then it pulls the appropriate user object and outputs the user information instead of the number. Lastly `format_date()` outputs a human friendly date represntation. These functions will be called when iterating over the returned calls list.

{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/pages/call_log_2.php %}
{% endhighlight %}

To begin constructing the actual content of the page we will use a div with the class `vbx-plugin`. This will give the page a nice look that will match the rest of OpenVBX. If you&rsquo;d like you can change the styles for this later. For headers in the page use the `<h3>` tag. For best organization we&rsquo;ll use a table.


{% highlight php startinline funcnamehighlighting  lineanchors tabsize=4 %}
{% include code/quickstart/pages/call_log_3.php %}
{% endhighlight %}

<a href="../" class="prev-page"><span></span> Getting Started</a>
<br class="clear" />
