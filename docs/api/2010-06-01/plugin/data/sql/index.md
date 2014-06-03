---
layout: docs
category: docs
---

**Plugin API &raquo; Data**

# SQL #

To make things easy, we've wrapped the common most used methods into a single class - OpenVBX.  
You can access users, groups, flows, run sql queries, etc.

### OpenVBX::query( _$sql_ )  ###

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
		<td></td>
		<td>Valid MySQL statement</td>
	</tr>
</tbody>
</table>

#### Returns ####

* Array of row results
* Empty Array if empty row result

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$results = OpenVBX::query("SELECT * FROM users");
{% endhighlight %}

### OpenVBX::one ( _$sql_ ) ###

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
		<td></td>
		<td>Valid MySQL statement</td>
	</tr>
</tbody>
</table>

#### Returns ####

* Associative array of single row result
* Empty Array if no result

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$results = OpenVBX::one("SELECT * FROM users WHERE email='adam@twilio.com'");
{% endhighlight %}
