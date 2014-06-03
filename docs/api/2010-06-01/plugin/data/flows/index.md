---
layout: docs
category: docs
---

**Plugin API &raquo; Data**

# Flows #

When you need to get information on a specific flow or you want to build a rewind plugin, use `OpenVBX::getFlows`.

### OpenVBX::getFlows( _$options = array(), $limit = -1, $offset = 0_ ) ###

`OpenVBX::getFlows` returns a list of flow objects based on the search options you supply.  
To search based on name, use `array('name' => 'my-flow')` as your search options parameter.

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
		<td>$options</td>
		<td>array</td>
		<td>Array of search options</td>
	</tr>
	<tr>
		<td>$limit</td>
		<td>integer</td>
		<td>Number of rows to return</td>
	</tr>
	<tr>
		<td>$offset</td>
		<td>integer</td>
		<td>Number of rows to skip in result, from beginning</td>
	</tr>
</tbody>
</table>

#### Returns ####

 * Array of user objects
 * Empty Array if empty row result

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$flows = OpenVBX::getFlows();
{% endhighlight %}

#### Examples ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
/* Get all flows that do not have an SMS Flow */
$flows = OpenVBX::getFlows(array('sms_data' => null));
foreach($flows as $flow) {
    var_dump($flow->name);
}
{% endhighlight %}

#### Output #####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
object(VBX_Flow)#81 (9) {
  ["table"]=>
  string(5) "flows"
  ["numbers"]=>
  array(0) {
  }
  ["fields"]=>
  array(5) {
	[0]=>
	string(2) "id"
	[1]=>
	string(4) "name"
	[2]=>
	string(4) "data"
	[3]=>
	string(8) "sms_data"
	[4]=>
	string(7) "user_id"
  }
  ["unique"]=>
  array(1) {
	[0]=>
	string(4) "name"
  }
  ["_instances":"VBX_Flow":private]=>
  NULL
  ["values"]=>
  array(6) {
	["id"]=>
	string(1) "1"
	["name"]=>
	string(18) "My super duper flow"
	["data"]=>
	string(0) ""
	["sms_data"]=>
	NULL
	["user_id"]=>
	string(1) "1"
	["tenant_id"]=>
	string(1) "1"
  }
  ["admin_fields"]=>
  array(0) {
  }
  ["_parent_name"]=>
  string(8) "VBX_Flow"
  ["tenant_id"]=>
  string(1) "1"
}
{% endhighlight %}
