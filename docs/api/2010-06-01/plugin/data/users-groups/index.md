---
layout: docs
category: docs
---

**Plugin API &raquo; Data**

# Users &amp; Groups #

### OpenVBX::getUsers( _$options = array(), $limit = -1, $offset = 0_ ) ###

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
$users = OpenVBX::getUsers();
{% endhighlight %}

#### Examples ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
/* Get list of users who haven't logged in yet and mail them a reminder */
$users = OpenVBX::getUsers(array('last_login' => null));

foreach($users as $user) {
    var_dump($user);
}
{% endhighlight %}

#### Output ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
object(VBX_User)#81 (6) {
    ["table"]=>
    string(5) "users"
    ["fields"]=>
    array(15) {
        [0]=>
        string(2) "id"
        [1]=>
        string(8) "is_admin"
        [2]=>
        string(9) "is_active"
        [3]=>
        string(10) "first_name"
        [4]=>
        string(9) "last_name"
        [5]=>
        string(8) "password"
        [6]=>
        string(11) "invite_code"
        [7]=>
        string(5) "email"
        [8]=>
        string(3) "pin"
        [9]=>
        string(12) "notification"
        [10]=>
        string(9) "auth_type"
        [11]=>
        string(9) "voicemail"
        [12]=>
        string(9) "tenant_id"
        [13]=>
        string(10) "last_login"
        [14]=>
        string(9) "last_seen"
    }
    ["admin_fields"]=>
    array(1) {
        [0]=>
        string(0) ""
    }
    ["values"]=>
    array(15) {
        ["id"]=>
        string(1) "2"
        ["is_admin"]=>
        string(1) "0"
        ["is_active"]=>
        string(1) "1"
        ["first_name"]=>
        string(4) "zomg"
        ["last_name"]=>
        string(4) "hair"
        ["password"]=>
        string(40) "270330338f214f211dc5f07820a602543d63367f"
        ["invite_code"]=>
        NULL
        ["email"]=>
        string(11) "ad@asdf.com"
        ["pin"]=>
        NULL
        ["notification"]=>
        NULL
        ["auth_type"]=>
        string(7) "openvbx"
        ["voicemail"]=>
        string(0) ""
        ["tenant_id"]=>
        string(1) "1"
        ["last_login"]=>
        NULL
        ["last_seen"]=>
        NULL
    }
    ["_parent_name"]=>
    string(8) "VBX_User"
    ["devices"]=>
    array(1) {
        [0]=>
        object(VBX_Device)#79 (8) {
            ["table"]=>
            string(7) "numbers"
            ["error_prefix"]=>
            string(0) ""
            ["error_suffix"]=>
            string(0) ""
            ["fields"]=>
            array(7) {
                [0]=>
                string(2) "id"
                [1]=>
                string(4) "name"
                [2]=>
                string(5) "value"
                [3]=>
                string(3) "sms"
                [4]=>
                string(8) "sequence"
                [5]=>
                string(9) "is_active"
                [6]=>
                string(7) "user_id"
            }
            ["values"]=>
            array(8) {
                ["id"]=>
                string(1) "1"
                ["name"]=>
                string(14) "Primary Device"
                ["value"]=>
                string(10) "+234234233"
                ["sms"]=>
                string(1) "1"
                ["sequence"]=>
                NULL
                ["is_active"]=>
                string(1) "1"
                ["user_id"]=>
                string(1) "2"
                ["tenant_id"]=>
                string(1) "1"
            }
            ["admin_fields"]=>
            array(0) {
            }
            ["_parent_name"]=>
            string(10) "VBX_Device"
            ["tenant_id"]=>
            string(1) "1"
        }
    }
}
{% endhighlight %}

### OpenVBX::getGroups( _$options = array(), $limit = -1, $offset = 0_ ) ###

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
$users = OpenVBX::getGroups();
{% endhighlight %}

#### Examples ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
/* Get a group with the name of Sales */
$groups = OpenVBX::getGroups(array('name' => 'Sales'));

if(!empty($groups)) {
    $group = current($groups);
    echo 'Found the group:'. $group->name;
}
{% endhighlight %}

#### Output ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
array(1) {
    [1]=>
    object(VBX_Group)#81 (7) {
        ["table"]=>
        string(6) "groups"
        ["fields"]=>
        array(3) {
            [0]=>
            string(2) "id"
            [1]=>
            string(4) "name"
            [2]=>
            string(9) "is_active"
        }
        ["admin_fields"]=>
        array(1) {
            [0]=>
            string(0) ""
        }
        ["values"]=>
        array(4) {
            ["id"]=>
            string(1) "1"
            ["name"]=>
            string(5) "Sales"
            ["is_active"]=>
            string(1) "1"
            ["tenant_id"]=>
            string(1) "1"
        }
        ["_parent_name"]=>
        string(9) "VBX_Group"
        ["tenant_id"]=>
        string(1) "1"
        ["users"]=>
        array(0) {
        }
    }
}
{% endhighlight %}
