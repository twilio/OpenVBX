---
layout: docs
category: docs
---

**Using Themes**

#Adding your Logo#

Adding your logo to OpenVBX is a great first step to making OpenVBX yours. We&rsquo;re going to change the logo from the regular one to something that is more monkey like. You can follow along with the logo supplied, or create your own. We need to put the logo into the images folder we created earlier that is inside our theme directory.

<img src="{{ site.baseurl }}/images/monkey-phone.png" />

Now in `style.css` we need to add some css code.

{% highlight css  lineanchors tabsize=4 %}
{% include code/quickstart/themes/style_1.css %} 
{% endhighlight %}

You&rsquo;re going to want to define the height and width the same as your image if you choose to use something other than our supplied monkey logo. When you save your changes we can set the new theme and you can see your custom logo. Just go to settings and select the name of your theme and you should see your logo.

<a href="../" class="prev-page"><span></span>Getting Started</a>
<a href="../themes-2/" class="next-page"><span></span>Changing The Colors</a>
<br class="clear" />
