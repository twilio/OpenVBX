---
layout: docs
category: docs
---

**Themes**

# Plugins #

### VBX Plugin Layout ###
	
Begin your plugin layout with the `.vbx-plugin` wrapper class. This will give your layout access to simple predefined styles that will help your plugins look great.

Remember, you can also override the core styles using your own theme.

#### Core Plugin Stylesheet ####

`http(s)://[install-root]/assets/c/plugin.css`


#### Example ####

Here is a sandbox example of common elements to use in your plugin layout.

{% highlight html startinline  lineanchors tabsize=4 %}
<div class="vbx-plugin">

    <ul>
        <li>Unordered Item</li>
        <li>Unordered Item</li>
    </ul>

    <ol>
        <li>Ordered Item</li>
        <li>Ordered Item</li>
    </ol>

    <table>
        <thead>
            <tr>
                <th>Column Heading 1</th>
                <th>Column Heading 2</th>
                <th>Column Heading 3</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Column 1</td>
                <td>Column 2</td>
                <td>Column 3</td>
            </tr>
        </tbody>
    </table>

    <form>
        <fieldset class="text">
            <label>Label
                <input type="text" />
            </label>
        </fieldset>

        <fieldset>
            <label>Label
                <select>
                    <option>Option</option>
                    <option>Option</option>
                </select>
            </label>
        </fieldset>

        <fieldset class="checkbox">
            <label>
                <input type="checkbox" /> Option
            </label>
            <label>
                <input type="checkbox" /> Option
            </label>
        </fieldset>

        <fieldset class="radio">
            <label>
                <input type="radio" name="bool" checked="checked" /> Yes
            </label>
            <label>
                <input type="radio" name="bool" /> No
            </label>
        </fieldset>

        <fieldset>
            <label>Message
                <textarea></textarea>
            </label>
        </fieldset>

        <button class="submit-button"><span>Submit</span></button>
    </form>
</div><!-- .vbx-plugin -->
{% endhighlight %}


### VBX Applet Layout ###

Since applets are placed within the flow editor, specific classes need to be used for certain elements to override core OpenVBX styles.



#### Core Applet Stylesheet ####

`http(s)://[install-root]/assets/c/applet.css`


#### Example ####


{% highlight html startinline  lineanchors tabsize=4 %}
<table class="vbx-items-grid">

    <thead>
        <tr>
            <th>Column Heading</th>
            <th>Column Heading</th>
        </tr>
    </thead>
    
    <tbody>
        <tr>
            <td>Column A</td>
            <td>Column B</td>
        </tr>
    </tbody>

</table><!-- .vbx-items-grid -->
{% endhighlight %}

Notice that there is not a form element present. Applets are already wrapped with a form, so there&rsquo;s no need here. Use fieldsets to create your form layouts inside of your applet.

{% highlight html startinline  lineanchors tabsize=4 %}
<fieldset class="vbx-applet-fieldset">
    
    <label>Label
        <input type="text" class="text" />
    </label>

    <label>Label
        <select>
            <option>Option</option>
            <option>Option</option>
        </select>
    </label>

    <label>
        <input type="checkbox" class="checkbox" /> Choice
    </label>

    <label>
        <input type="checkbox" class="checkbox" /> Choice
    </label>

    <label>
        <input type="radio" class="radio" name="bool" /> Yes
    </label>

    <label>
        <input type="radio" class="radio" name="bool" /> No
    </label>

    <label>
        <textarea class="medium">Medium Textarea</textarea>
    </label>

    <label>
        <textarea class="large">Large Textarea</textarea>
    </label>
    
</fieldset><!-- .vbx-applet-fieldset -->
{% endhighlight %}

