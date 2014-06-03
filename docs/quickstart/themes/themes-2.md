---
layout: docs
category: docs
---

**Using Themes**

#Changing the Colors#

This part of the quickstart we&rsquo;re going to change the colors of your install. Continuing with the monkey theme, we&rsquo;re going to make our subnav look like a big banana, because monkeys love bananas.
When styling your OpenVBX theme you need to declare a custom class for your theme. In this case we&rsquo;re going to use the class monkey-theme. You can name your class what ever you want as long as it&rsquo;s followed by `-theme`. 

<h3>Styling The Links</h3>

{% highlight css  lineanchors tabsize=4 %}
{% include code/quickstart/themes/style_2.css %} 
{% endhighlight %}

First we&rsquo;re just going to change the link color to something different than the regular link color. These colors are what the default links will look like.

<h3>Styling The Main Navigation</h3>

{% highlight css  lineanchors tabsize=4 %}
{% include code/quickstart/themes/style_3.css %} 
{% endhighlight %}

Now we&rsquo;re going to change the side nav. The side nav has many elements, in order to style it we only need to change three of them. 

 *  `#vbx-main-nav .vbx-nav-title` - This style defines the nav title. This needs a background color and a font color.
 *  `#vbx-main-nav .vbx-nav-item a` - This style defines the regular links in the main nav. In addition to changing the background color and font color we are changing the border bottom color. 
 *  `#vbx-main-nav .selected a, #vbx-main-nav .selected a:hover` - This style changes the background color and font color for the selected item in the main nav.

<h3>Styling The Tabs</h3>

{% highlight css  lineanchors tabsize=4 %}
{% include code/quickstart/themes/style_4.css %} 
{% endhighlight %}

Finally we&rsquo;re going to change the styles for the content tabs. Tabs can be used in many different ways. If you want to see what the tabs look like go to the settings page. When designing a new color scheme for the tabs there are four styles to use.

 *  `.vbx-content-tabs` - This style defines the bar that everything is placed on. 
 *  `.vbx-content-heading` - This style is added to the h2. It defines what the tabs header will look like.
 *  `.vbx-content-tabs li a:link, .vbx-content-tabes li a:visited` - This style defines the tabs that are not selected.
 *  `.vbx-content-tabs li.selected a:link, .vbx-content-tabs li.selected a:visited` - This style defines the tab that is selected. In this example we&rsquo;ll keep the background color the same as the background of the page.

<a href="{{ site.baseurl }}/docs/quickstart/themes/themes-1/" class="prev-page"><span></span>Adding your Logo</a> 
<br class="clear" />
