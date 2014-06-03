---
layout: docs
category: docs
---

**Plugin API &raquo; Utilies**

# Pages #

OpenVBX Page methods will allow you to build tightly integrated services into your pages.  If you need to grab the current user, display notifications to the user, and add javascript to your plugin.


### OpenVBX::getCurrentUser() ###

When you need to do something with the current user, use this method.  It returns a vbx_user object.

#### Arguments ####

None

#### Returns ####

* user object

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
$user = OpenVBX::getCurrentUser();
echo $user->id;
echo $user->email;
if($user->is_admin) {
	echo $user->first_name . ' is an admin';
}
{% endhighlight %}

#### Example ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
var_dump(OpenVBX::getCurrentUser());
{% endhighlight %}

#### Output ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
    object(VBX_User)#78 (6) {
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
        string(40) ""
        ["invite_code"]=>
        NULL
        ["email"]=>
        string(15) "adam@example.com"
        ["pin"]=>
        NULL
        ["notification"]=>
        NULL
        ["auth_type"]=>
        string(7) "openvbx"
        ["voicemail"]=>
        string(38) "Please leave a message after the beep."
        ["tenant_id"]=>
        string(1) "1"
        ["last_login"]=>
        string(19) "2010-06-15 12:06:18"
        ["last_seen"]=>
        string(19) "2010-06-15 12:15:17"
      }
      ["_parent_name"]=>
      string(8) "VBX_User"
      ["devices"]=>
      array(0) {
      }
    }
{% endhighlight %}


### OpenVBX::isAdmin() ###

Use this helper method when you want to know if the current session is within the administrative role.  This is great for those pages that you only want administrators to access.

#### Arguments ####

None

#### Returns ####

* Boolean - True if session is administrative

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
OpenVBX::isAdmin()
{% endhighlight %}

#### Example ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
if( OpenVBX::isAdmin() ) {
    include('admin.php');
} else {
    include('user.php');
}
{% endhighlight %}


### OpenVBX::setNotificationMessage( _$message_ ) ###

Notify the user using the standard yellow message bar in the OpenVBX UI.

#### Arguments ####

$message &mdash; String of message

#### Returns ####

* None

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
OpenVBX::setNotificationMessage('Make it so');
{% endhighlight %}

#### Output ####

![Notification Message]({{ site.baseurl }}/images/docs/notificationMessage.png "Notification Message")


### OpenVBX::addJS( _$filename_ ) ###

If you need to add javascript to your plugin, use this method.  It adds javascript the right way, letting you do efficient UI programming without all the leg work.
It will pick up the javascript file from your plugin directroy, so you just supply the relative path to the file.

The javascript file will be included at the end of the document, just before the closing body tag of the document.  You will have jQuery available at this point in time.

#### Arguments ####

$filename &mdash; string of filename relative to the plugin directory

#### Returns ####

* None

#### Usage ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
OpenVBX::addJS('my-monkey.js');
{% endhighlight %}

#### Output ####

{% highlight php startinline funcnamehighlighting lineanchors tabsize=4 %}
<script type="text" src="http://example.com/plugins/my-plugin/my-monkey.js"></script>
{% endhighlight %}
