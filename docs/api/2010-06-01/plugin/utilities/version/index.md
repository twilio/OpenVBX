---
layout: docs
category: docs
---

**Plugin API &raquo; Utilities**

# Version #

OpenVBX believes in uptime, so we've exposed a number of versioning methods to allow developers to keep track of running installations, schemas, upcoming upgrade versions, and twilio api versions.

### OpenVBX::version( ) ###

Get the version string of OpenVBX. 

#### Arguments ####

None

#### Returns ####

* string of OpenVBX version

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
OpenVBX::version();
{% endhighlight %}

### OpenVBX::schemaVersion( ) ###

Get the current installed schema version.  Use this when you depend on specific versions of the schema and want your plugins to work many versions.

#### Arguments ####

None

#### Returns ####

* Integer of SchemaVersion

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
OpenVBX::schemaVersion();
{% endhighlight %}

#### Examples ####

Use a feature that works in newer schema versions of OpenVBX.

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$currentSchemaVersion = OpenVBX::schemaVersion();

if($currentSchemaVersion > 26) {
	/* Do something only supported in schemas newer than version 26. */
}
{% endhighlight %}

### OpenVBX::getLatestSchemaVersion( ) ###

Get the latest schema version available in the installation.  This is not the current schema version installed.
This is the version of the schema that is available for upgrade, but hasn't been done so.  You may want to write an upgrade utility using this.

#### Arguments ####

None

#### Returns ####

* Integer of SchemaVersion

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
OpenVBX::getLatestSchemaVersion();
{% endhighlight %}

#### Examples ####

Compare versions of the schema to see if the installation is up to date.

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$latestSchemaVersion = OpenVBX::getLatestSchemaVersion();
$currentSchemaVersion = OpenVBX::schemaVersion();

if($latestSchemaVersion > $currentSchemaVersion) {
	error_log('You are out of date');
}
{% endhighlight %}

### OpenVBX::getTwilioApiVersion( ) ###

Gets the date of the current Twilio API.  

#### Arguments ####

None

#### Returns ####

* string of Twilio API version

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
OpenVBX::getTwilioApiVersion();
{% endhighlight %}

#### Examples ####
	
{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
echo OpenVBX::getTwilioApiVersion();
{% endhighlight %}

#### Output ####

Returns a date version of the twilio api.

    2010-04-01