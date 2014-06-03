---
layout: docs
category: docs
---

**Themes**

# Layout #


### YUI Templates ###

Columns and grids are created using [YUI 2 CSS Grids](http://developer.yahoo.com/yui/grids/)

{% highlight html startinline  lineanchors tabsize=4 %}
<div id="doc3" class="yui-t2">

    [Wrapper]

</div>
{% endhighlight %}

YUI Grids CSS declares #doc3 as a full width layout, while `.yui-t2` sets up a left column of 180px.


### Wrapper ###

The #wrapper holds the contents of the selected layout.

{% highlight html startinline  lineanchors tabsize=4 %}
<div id="wrapper" class="[theme-name]-theme">

    [Header, Body and Footer]

</div>
{% endhighlight %}


### Header ###

This section contains the OpenVBX logo

{% highlight html startinline  lineanchors tabsize=4 %}
<div id="hd">

    [OpenVBX Logo]

</div>
{% endhighlight %}

_You may override this image with your own_


### Utility Menu ###

The utility menu holds common application links and the user indicator.

{% highlight html startinline  lineanchors tabsize=4 %}
<div id="main-util-menu">

    [Username, My Account and Log Out]

</div>
{% endhighlight %}


### Context Menu ###

Call and SMS buttons are present throughout the application.

{% highlight html startinline  lineanchors tabsize=4 %}
<div id="vbx-context-menu" class="context-menu">

    [Call and SMS Buttons]

</div>
{% endhighlight %}

_Notifications also appear in the Context Menu_


### Body ###

The #bd holds two blocks, VBX Main and VBX Sidebar. YUI Grids CSS assigns these two blocks (`.yui-b`) specific rules based on the template used for the layout. In most cases, the `.yui-t2` template is used, declaring VBX Sidebar as a 180px wide block on the left. [See YUI Grids CSS: Preset Template 2](http://developer.yahoo.com/yui/examples/grids/grids-t2.html)

{% highlight html startinline  lineanchors tabsize=4 %}
<div id="bd">

    <div id="yui-main">

        <div class="yui-b">

            [VBX Main]

        </div>

    </div>

    <div class="yui-b">

        [VBX Sidebar]

    </div>

</div>
{% endhighlight %}

Most of your view is contained within the VBX Main section. Content Menus are also located inside this section to provide action buttons, depending on the view presented. The main navigation is located inside of the VBX Sidebar. Main Navigation contains section headings and unordered lists for navigation items.


### Footer ###

The Footer contains version, copyright and links to Terms and Privacy for Twilio Inc.

{% highlight html startinline  lineanchors tabsize=4 %}
<div class="ft">

    [Version, Copyright and Links]

</div>
{% endhighlight %}

