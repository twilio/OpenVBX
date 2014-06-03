---
layout: docs
category: docs
---

# Themes #


## Anatomy of OpenVBX ##

OpenVBX uses YUI 2 CSS Grids to create the document layout and columns. Preset templates also determine how grids are created. Core styles are always rendered for OpenVBX, and the theme contains a style.css file that is used to override these core styles. There are specific styles setup for the main content of OpenVBX, as well as plugins and applets. Building your plugin layout is simplified with core OpenVBX styles. 

* [Layout]({{ site.baseurl }}/docs/themes/layout/)
* [Stylesheets]({{ site.baseurl }}/docs/themes/stylesheets/)
* [UI]({{ site.baseurl }}/docs/themes/ui/)
* [Plugins]({{ site.baseurl }}/docs/themes/plugins/)


### Building a Theme ###

If you have designed themes for other applications, building a theme for OpenVBX will be familiar to you. If you are new, don&rsquo;t worry, it&rsquo;s pretty easy.

1. **Create a new directory for your theme.**

	> After installing OpenVBX, locate the directory `/assets/themes`.  
	> Make a new directory in here, using your theme name.  
	> Now you have a theme directory: `/assets/themes/my-first-theme`

2. **Create style.css inside of your new theme directory.**

	> Use `style.css` to override core OpenVBX styles.

3. **Save all files inside your theme directory**

	> Create an images directory inside of your theme to save images there.

