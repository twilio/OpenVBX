# OpenVBX Install Guide

[http://www.openvbx.org](http://www.openvbx.org)

Installing OpenVBX is quick and easy, just follow this five step guide to get up and running in no time.


## Requirements

* Web Server
* MySQL 5+
* PHP 5.2.3+
* [Twilio Account](https://www.twilio.com/try-twilio)


## Step 1: Get the Code

[Download](http://www.openvbx.org/download) the latest release and unpack the source code into your webroot.


## Step 2: Create a Database

OpenVBX needs a database from either your hosting provider or your own web server. Please see your hosting provider's documentation on creating databases for more info.


## Step 3: Run the Installer

Open your web browser and navigate to the URL of your OpenVBX installation. The installer will check that your system meets the minimum requirements and will configure your new phone system. You may have to adjust the permissions on the OpenVBX upload and configuration directories.


## Step 4: Connect to Twilio

During the install process, you will be prompted for your Twilio API credentials. You can obtain your _Account SID_ and _Auth Token_ from your [Twilio Dashboard](https://www.twilio.com/user/account/). You must be logged into your Twilio account to access the dashboard. If you don't have a Twilio account [register for a free trial](https://www.twilio.com/try-twilio).


## Step 5: Login

Navigate to the URL of your OpenVBX installation and login using the account you created during the installation. Once you're logged in you'll be able to add users and groups to your new phone system. You can also add devices, provision phone numbers, configure voicemail, and design call flows.


## Step 6: Profit!

__That's it, you're all set.__  
OpenVBX is open source and extensible so feel free to skin it, hack it, and sell it!


## Installing on Godaddy

OpenVBX does not function properly on GoDaddy without Mod Rewrite. Make sure that root directory of your OpenVBX install contains the `.htaccess` file that was distributed with OpenVBX. If not, copy the `htaccess_dist` file to `.htaccess`.

Add this to the bottom of `OpenVBX/config/config.php`:

	$config['uri_protocol'] = 'REQUEST_URI';
	$config['index_page'] = '';

If you're still having issues at this point, modify the `IfModule mod_rewrite.c` section of your `.htaccess` file to be the following:

	RewriteEngine ON

	RewriteCond %{REQUEST_FILENAME} !-f 
	RewriteCond %{REQUEST_FILENAME} !-d 
	RewriteRule ^(.*)$ /index.php?$1 [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L,QSA] 
	ErrorDocument 404 /fallback/rewrite.php


## More Resources

Now that you've got a working installation you can:

* Extend OpenVBX by installing a plugin or writing your own - [http://www.openvbx.org/plugins](http://www.openvbx.org/plugins)
* Scratch your own itch and help improve OpenVBX - [http://www.openvbx.org/get-involved/](http://www.openvbx.org/get-involved/)
* Read the documentation - documents [http://www.openvbx.org/docs](http://www.openvbx.org/docs)
* Get support - [http://getsatisfaction.com/openvbx](http://getsatisfaction.com/openvbx)


----

# OpenVBX Step by Step Explanation


## About

This page provides detailed information for each step of the OpenVBX install process.


## OpenVBX Server Check

OpenVBX requires the software listed below. It is all available for free and is open source. OpenVBX is supported and should run on all major linux distributions. OpenVBX may run on other platforms (namely Windows) but is currently unsupported.

1. **PHP version:** We recommend PHP 5.2.3 or higher
1. **CURL support:** OpenVBX requires CURL. If you don't meet this requirement, install the CURL module.
1. **Apache version:** We recommend Apache version 2.2+. Earlier versions and other web servers may work, but are currently unsupported.
1. **MySQL support:** We require MySQL version 5+.
1. **APC support (optional):** APC is recommended and can be used for caching, but not required.
1. **Memcache support (optional):** Memcache can be used for caching, but is not required.
1. **Config directory writable:** The configuration directory must be writable by the user your webserver is running as for the OpenVBX installation to complete. The path to the configuration directory is `<webroot>/OpenVBX/config`. On unix systems you can adjust the permissions with the `chown` and `chmod` commands.
1. **SimpleXML support:** OpenVBX requires SimpleXML. If you don't meet this requirement, install the SimpleXML module.
1. **JSON support:** OpenVBX requires JSON. If you don't meet this requirement install the JSON module.
1. **Upload directory writable:** The upload directory must be writable by the user your webserver is running as for the OpenVBX installation to complete. The path to the configuration directory is `<webroot>/audio-uploads`. On unix systems you can adjust the permissions with the `chown` and `chmod` commands.
1. **.htaccess File:** OpenVBX works best with Mod Rewrite enabled. If you've uploaded the contents of OpenVBX using an FTP application then its possible that the .htaccess file was ignored. If the .htaccess file is missing from your install copy the `htaccess_dist` file to `.htaccess` before beginning the install process. Some hosts do not run OpenVBX properly with Mod Rewrite disabled.


## Configure Database

OpenVBX requires a MySQL database. You should create a database, and a user with permissions to access the database for OpenVBX. If you are running OpenVBX on a shared hosting environment, you may have to use the tools provided by your hosting provider to create a new database and database user.

1. **Hostname:** In most cases this should be `localhost`. If your database server is on a machine other than your webserver, specify it's address here.
1. **Username:** The username to use when connecting to MySQL
1. **Password:** The password to use when connecting to MySQL
1. **Database Name:** The name of your OpenVBX database.


## Connect to your Twilio account

OpenVBX requires a Twilio account to enable provisioning phone numbers, sending and receiving voice calls, and sending and receiving SMS. If you don't have a Twilio account, [register for a free trial](https://www.twilio.com/try-twilio).

1. **Twilio SID:** This is your account identifier, it is unique to you and can be shared.
1. **Twilio Token:** This is the key to your Twilio account, it is private and should not be shared.

If you plan on hosting multiple tenants on your install and would like each tenant's activity to be billed to their account instead of to your account you can set up a Connect Application via your Twilio Dashboard.

When creating your application use these settings:

1. **Friendly Name:** Any name that makes sense to you.
1. **Company Name & Description:** _Optional_. Anything that makes sense to you.
1. **Homepage URL & Authorize URL:** The full url to your webroot. ie: _http://example.org_ - these will be updated by OpenVBX during the install process.
1. **Deauthorize URL:** _Leave blank_. This will be set by OpenVBX during install.
1. **Access Required:** Select "Read all account data" & "Charge account for usage".


## Optional Settings

OpenVBX has the ability to send email notifications to users. This includes password reset emails, voicemail and SMS notifications, as well as notifications defined by plugins.

1. **From Email:** This email address will be used in the from field of emails sent from your OpenVBX installation.


## Setup Your Account

In order to administer your OpenVBX installation you'll need to create a user account. This is the account that will be used to manage your OpenVBX installation. This information can be updated later from within OpenVBX.

1. **Email:** The email address to be associated with your OpenVBX admin account.
1. **Password:** The password for your OpenVBX admin account.
1. **First Name:** The first name of your OpenVBX administrator.
1. **Last Name:** The last name of your OpenVBX administrator.
