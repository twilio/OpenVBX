---
layout: docs
category: docs
---

**Themes**

# UI #


### VBX Main ###

VBX Main is nested inside of the `#bd > #yui-main > .yui-b` block of OpenVBX. See [Layout]({{ site.baseurl }}/docs/themes/layout/).

This is an example of the Inbox, where `.vbx-items-grid` is used to display rows of messages. Here `.vbx-content-menu` holds buttons for selecting messages and deleting them as well.

{% highlight html startinline  lineanchors tabsize=4 %}
<div id="vbx-main">
    <div class="vbx-content-main">

        <div class="vbx-content-menu">
            [Buttons]
        </div>

        <table class="vbx-items-grid">
            [Messages]
        </table>

    </div>
</div>
</pre>
{% endhighlight %}


### VBX Sidebar ###

The VBX Sidebar layout is determined by the YUI template being used for each specific view. Using `.yui-t2` will place the sidebar to the left with a 180px width.

{% highlight html startinline  lineanchors tabsize=4 %}
<div id="vbx-sidebar">
    <div id="vbx-main-nav">

        <h3 class="vbx-nav-title">[Title]</h3>

        <ul class="vbx-main-nav-items">
            [Navigation Links]
        </ul>

    </div>
</div>
{% endhighlight %}

_Some views do not render the VBX Sidebar, ie. Flows_



### VBX Plugin ###

See [Themes &raquo; Plugins]({{ site.baseurl }}/docs/themes/plugins) for plugin and applet layout examples.

### Buttons ###

Button styling is applied to the button tag globally. A combination of a button tag and nested span tag, are used to create a sliding doors effect. There are several button types that use custom background image sprites to display hover and active states.

#### Normal Buttons ####

{% highlight html startinline  lineanchors tabsize=4 %}
<button class="normal-button">
    <span>Button Label</span>
</button>
{% endhighlight %}

#### Submit Buttons ####

{% highlight html startinline  lineanchors tabsize=4 %}
<button class="submit-button" type="submit">
    <span>Submit</span>
</button>
{% endhighlight %}

#### Call and SMS Buttons ####

{% highlight html startinline  lineanchors tabsize=4 %}
<button class="call-button">
    <span>Call</span>
</button>

<button class="sms-button">
    <span>SMS</span>
</button>
{% endhighlight %}

#### Link Buttons ####

Several links are styled to represent buttons. The quick Call and SMS links are used in the VBX Items Grid view for the Inbox.

{% highlight html startinline  lineanchors tabsize=4 %}
<a href="" class="quick-call-button">Call</a>

<a href="" class="quick-sms-button">SMS</a>
{% endhighlight %}


### Forms ###

OpenVBX Forms are given a common class name of `vbx-form` to specify custom styling. Fieldsets are used to create containers within an OpenVBX form. Labels are also assigned a class of `field-label`. Input fields are named according to their widths: `tiny`, `small`, `medium` and `large`.

{% highlight html startinline  lineanchors tabsize=4 %}
<form class="vbx-form">

    <fieldset class="vbx-input-container">

        <label class="field-label">
            <input class="medium" type="text" />
        </label>

        <label class="field-label">
            <input class="small" type="text" />
        </label>

    </fieldset>

</form>
{% endhighlight %}


### Controls ###

#### Actions ####

Icons represent common actions across OpenVBX. The icon set is created with 24 &times; 24px image sprites.

{% highlight html startinline  lineanchors tabsize=4 %}
<a href="" class="add action">
    <span class="replace">Add</span>
</a>

<a href="" class="close action">
    <span class="replace">Close</span>
</a>
{% endhighlight %}

_Other actions include: remove, trash and edit._


#### Pagination ####

Pagination is used with VBX Items Grid table views. For example, the Inbox view displays 20 items before displaying the pagination control.

{% highlight html startinline  lineanchors tabsize=4 %}
<div class="pagination">

    <span class="current">1</span>

    <span class="num">2</span>

    <span class="num">3</span>

    <span class="next">Next</span>

    <span class="last">Last</span>

</div>
{% endhighlight %}


### Dialogs ###

Dialogs are using jQuery `ui-dialog` class names.

{% highlight html startinline  lineanchors tabsize=4 %}
<div class="ui-dialog">

    <div class="ui-dialog-titlebar">Title</div>

    <div class="ui-dialog-content">A paragraph</div>

    <div class="ui-dialog-buttonpane">
        <button>OK</button>
        <button>Cancel</button>
    </div>
</div>
{% endhighlight %}
