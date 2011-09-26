# OpenVBX Install Guide
http://www.openvbx.org

Installing OpenVBX is quick and easy, just follow this five step guide to get up and running in no time.

# Requirements
* Web Server
* MySQL 5+
* PHP 5.2 recommended _using less than PHP 5.2 requires [PEAR Services_JSON](http://pear.php.net/package/Services_JSON)_
* [Twilio Account](https://www.twilio.com/try-twilio) OR [Tropo Account](https://www.tropo.com/) 

# Step 1: Get the Code
[Download](http://www.openvbx.org/download) the latest release and unpack the source code into your webroot.

# Step 2: Create a Database
OpenVBX needs a database from either your hosting provider or your own web server.  Please see your hosting provider's documentation on creating databases for more info.

# Step 3: Run the Installer
Open your web browser and navigate to the URL of your OpenVBX installation. The installer will check that your system meets the minimum requirements and will configure your new phone system. You may have to adjust the permissions on the OpenVBX upload and configuration directories.

# Step 4a: Connect to Twilio
During the install process, you will be prompted for your Twilio API credentials. You can obtain your _Account SID_ and _Auth Token_ from your [Twilio Dashboard](https://www.twilio.com/user/account/). You must be logged into your Twilio account to access the dashboard. If you don't have a Twilio account [register for a free trial](https://www.twilio.com/try-twilio) and we'll include $30.00 worth of credit to help you get started.

# Step 4b: Connect to Tropo
During the install process, you will be prompted for your Tropo credentials. You can obtain your Tropo login information from [Tropo.com](https://www.tropo.com/). If you don't have a Tropo account [register for a free developer account](https://www.tropo.com/account/register.jsp).

# Step 4c: Connect to VoiceVault
During the install process, you will be prompted for your VoiceVault credentials. You can obtain your VoiceVault credentials information from www.voicevault.com. If you don't have a VoiceVault account [register for a free developer account](https://development.voicevault.net/Registration/). VoiceVault is optional and adds Voice Biometric authentication for password resets. 

# Step 5: Login
Navigate to the URL of your OpenVBX installation and login using the account you created during the installation. Once you're logged in you'll be able to add users and groups to your new phone system. You can also add devices, provision phone numbers, configure voicemail, and design call flows.

# Step 6: Profit!
__That's it, you're all set.__
OpenVBX is open source and extensible so feel free to skin it, hack it, and sell it!

# Installing on Godaddy

Add this to the bottom of OpenVBX/config/config.php
$config['uri_protocol'] = 'REQUEST_URI';
$config['index_page'] = '';


# More Resources
Now that you've got a working installation you can:
* Extend OpenVBX by installing a plugin or writing your own - http://www.openvbx.org/plugins
* Scratch your own itch and help improve OpenVBX - http://www.openvbx.org/get-involved/
* Read the documentation - documents http://www.openvbx.org/docs
* Get support - http://getsatisfaction.com/support


----

OpenVBX Step by Step Explanation

# About
This page provides detailed information for each step of the OpenVBX install process.

# OpenVBX Server Check
OpenVBX requires the software listed below. It is all available for free and is open source. OpenVBX is supported and should run on all major linux distributions. OpenVBX may run on other platforms (namely Windows) but is currently unsupported.

1. PHP version
 * We recommend PHP 5.2 or higher, although you can get away with earlier versions as long as you have [PEAR Services_JSON](http://pear.php.net/package/Services_JSON) installed
1. CURL support
 * OpenVBX requires CURL. If you don't meet this requirement, install the CURL module.
1. Apache version
 * We recommend Apache version 2.2+. Earlier versions and other web servers may work, but are currently unsupported.
1. MySQL support
 * We require MySQL version 5+.
1. APC support (optional)
 * APC is recommended, but not required.
1. Config directory writable
 * The configuration directory must be writable by the user your webserver is running as for the OpenVBX installation to complete. The path to the configuration directory is '<webroot>/OpenVBX/config'. On unix systems you can adjust the permissions with the `chown` and `chmod` commands.
1. SimpleXML support
 * OpenVBX requires SimpleXML. If you don't meet this requirement, install the SimpleXML module.
1. JSON support
 * OpenVBX requires JSON. If you don't meet this requirement, install the JSON module. For versions of PHP prior to 5.2 you will need the [PEAR Services_JSON](http://pear.php.net/package/Services_JSON) PECL module installed.
1. Upload directory writable
 * The upload directory must be writable by the user your webserver is running as for the OpenVBX installation to complete. The path to the configuration directory is '<webroot>/OpenVBX/audio-uploads'. On unix systems you can adjust the permissions with the `chown` and `chmod` commands.

# Configure Database
OpenVBX requires a MySQL database. You should create a database, and a user with permissions to access the database for OpenVBX. If you are running OpenVBX on a shared hosting environment, you may have to use the tools provided by your hosting provider to create a new database and database user.

1. Hostname
 * In most cases this should be 'localhost'. If your database server is on a machine other than your webserver, specify it's address here.
1. Username
 * The username to use when connecting to MySQL
1. Password
 * The password to use when connecting to MySQL
1. Database Name
 * The name of your OpenVBX database.

# Connect to your Twilio account
OpenVBX requires a Twilio account to enable provisioning phone numbers, sending and receiving voice calls, and sending and receiving SMS from Tropo. If you don't have a Twilio account, [register for a free trial](https://www.twilio.com/try-twilio) and we'll include $30.00 worth of credit to help you get started.

1. Twilio SID
 * This is your account identifier, it is unique to you and can be shared.
1. Twilio Token
 * This is the key to your Twilio account, it is private and should not be shared.

# Connect to your Tropo account
OpenVBX requires a Tropo account to enable provisioning phone numbers, sending and receiving voice calls, and sending and receiving SMS from Tropo. If you don't have a Tropo account, [register for a free trial](https://www.tropo.com/account/register.jsp).

1. Tropo username
 * This is your unique Tropo.com username.
1. Tropo password
 * This is the password to your Tropo.com account.
1. Phono key
 * This is your Phono API key from [phono.com](http://phono.com). This is needed to use the Phono web client for calls.

# Connect to your VoiceVault account
If you want to use the Voice Biometric authentication for password reset functionality in OpenVBX you have to add a VoiceVault account to enable this functionality. If you don't have a VoiceVault account, [register for a free developer account](https://development.voicevault.net/Registration/).

1. VoiceVault Username
 * This is your unique VoiceVault API username.
1. VoiceVault password
 * This is the password to your VoiceVault API account.
1. VoiceVault Config
 * This is the VoiceVault config ID you received when you registered for API access.
1. VoiceVault Organisation
 * This is the VoiceVault Organisation ID you received when you registered for API access.
1. Phone Number
 * After you finish the installation and add phone numbers to OpenVBX you can complete the VoiceVault installation on the API Accounts tab by selecting the Phone Number, this is the phone number calls will originate from when a user requests a password reset by phone.

# Optional Settings
OpenVBX has the ability to send email notifications to users. This includes password reset emails, voicemail and SMS notifications, as well as notifications defined by plugins.

1. From Email
 * This email address will be used in the from field of emails sent from your OpenVBX installation.

# Setup Your Account
In order to administer your OpenVBX installation you'll need to create a user account. This is the account that will be used to manage your OpenVBX installation. This information can be updated later from within OpenVBX.

1. Email
 * The email address to be associated with your OpenVBX admin account.
1. Password
 * The password for your OpenVBX admin account.
1. First Name
 * The first name of your OpenVBX administrator.
1. Last Name
 * The last name of your OpenVBX administrator.

