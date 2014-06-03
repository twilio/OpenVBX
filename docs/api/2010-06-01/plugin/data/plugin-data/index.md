---
layout: docs
category: docs
---

**Plugin API &raquo; Data**

# Plugin Data #

PluginData contains helpers to set key/value pairs scoped to your own plugin and run sql queries directly on the OpenVBX database.

Use `PluginData::set()` to store values, `PluginData::get()` to retrieve, and `PluginData::delete()` to remove.

### PluginData::set( _$key, $value_ ) ###

`PluginData::set()` sets a key/value pair into your plugin.  You can do this from within a Page, Applet UI and TwiML.

#### Arguments ####

<table class="parameters">
<thead>
	<tr>
		<th class="col-1">Name</th>
		<th class="col-2">Type</th>
		<th class="col-3">Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>$key</td>
		<td>string</td>
		<td>Key of your key value pair.	 </td>
	</tr>
	<tr>
		<td>$value</td>
		<td>mixed</td>
		<td>This can be any type of variable. Whatever you store is what should come out when you use Plugin::get()</td>
	</tr>
</tbody>
</table>

#### Returns ####

* None

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
PluginData::set("mykey", "value");
{% endhighlight %}

#### Examples ####

_Set some values in an array and store in the PluginData._ 

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
PluginData::set("my-array", array(1,2,3,4));
{% endhighlight %}

_Store a newly created object in the PluginData._

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$obj = new stdClass();
$obj->property = 'test';
PluginData::set("my-object", $obj);
{% endhighlight %}

### PluginData::get( _$key, $default = null_ ) ###

If default is specified, a default return value will be returned when key is not found in PluginData.

#### Arguments ####

<table class="parameters">
<thead>
	<tr>
		<th class="col-1">Name</th>
		<th class="col-2">Type</th>
		<th class="col-3">Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>$key</td>
		<td>string</td>
		<td>The key of the data you want to fetch.	Must be a string value.</td>
	</tr>
	<tr>
		<td>$default</td>
		<td>mixed</td>
		<td>$default is returned when the key is not set.  Can be any type of php variable.</td>
	</tr>
</tbody>
</table>

#### Returns ####

* mixed &mdash; value stored in PluginData by $key

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$value = PluginData::get("mykey");
{% endhighlight %}

#### Examples ####

Get some values in an array and store in the PluginData, retrieve them and list them out.

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
PluginData::set("my-array", array(1,2,3,4));
$value = PluginData::get("my-array");
print_r($value);
{% endhighlight %}

#### Output ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
Array
(
    [0] => 1
    [1] => 2
    [2] => 3
    [3] => 4
)
{% endhighlight %}

Get a list of strings from the garage key.

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
PluginData::set('garage', array('cars', 'tires', 'bikes'));
var_dump(PluginData::get('garage'));
{% endhighlight %}

#### Output ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
array(3) {
    [0]=>
    string(4) "cars"
    [1]=>
    string(5) "tires"
    [2]=>
    string(5) "bikes"
}
{% endhighlight %}


### PluginData::delete( _$key_ )  ###

Use this method to delete a key/value pair from your key/value store.

#### Arguments ####

<table class="parameters">
<thead>
	<tr>
		<th class="col-1">Name</th>
		<th class="col-2">Type</th>
		<th class="col-3">Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>$key</td>
		<td>string</td>
		<td>The key you want to delete from the key/value store.</td>
	</tr>
</tbody>
</table>

#### Returns ####

* void

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
PluginData::delete("my-key");
{% endhighlight %}


### PluginData::sqlQuery( _$sql_ )  ###

Use this to run a sql query on the OpenVBX database.

#### Arguments ####

<table class="parameters">
<thead>
	<tr>
		<th class="col-1">Name</th>
		<th class="col-2">Type</th>
		<th class="col-3">Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>$sql</td>
		<td>string</td>
		<td>Valid MySQL statement</td>
	</tr>
</tbody>
</table>

#### Returns ####

* Array of row results
* Empty Array if empty row result

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$results = PluginData::sqlQuery("SELECT * FROM users");
{% endhighlight %}

#### Examples ####

Get a user from the users table 

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$user = OpenVBX::getCurrentUser();
$user_id = intval($user->id);
$result = PluginData::sqlQuery("SELECT * FROM users WHERE id=$user_id");

var_dump($result);
{% endhighlight %}

#### Output ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
array(1) {
  [0]=>
  array(15) {
    ["id"]=>
    string(1) "1"
    ["is_admin"]=>
    string(1) "1"
    ["is_active"]=>
    string(1) "1"
    ["first_name"]=>
    string(4) "Adam"
    ["last_name"]=>
    string(0) ""
    ["password"]=>
    string(40) "0ff27dad6c53367331acd54b3d0dbc0f9ca4eab0"
    ["invite_code"]=>
    NULL
    ["email"]=>
    string(15) "adam@twilio.com"
    ["pin"]=>
    NULL
    ["notification"]=>
    NULL
    ["auth_type"]=>
    string(1) "1"
    ["voicemail"]=>
    string(38) "Please leave a message after the beep."
    ["tenant_id"]=>
    string(1) "1"
    ["last_seen"]=>
    string(19) "2010-06-15 10:22:23"
    ["last_login"]=>
    string(19) "2010-06-15 07:38:40"
  }
}
{% endhighlight %}
