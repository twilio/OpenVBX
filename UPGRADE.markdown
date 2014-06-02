# OpenVBX Upgrade Guide

[http://www.openvbx.org](http://www.openvbx.org)

Upgrading OpenVBX is easy, just follow the steps below to upgrade your install.


## Backup First

The first thing to before upgrading any software is to make backup copies of your code and your data. How you can accomplish this depends on your hosting provider. If you have shell access you can just use mysqldump to make an export of your database. If you don't have shell access than you probably have a control panel like cPanel.

Make a backup copy of your database and your OpenVBX installation because there are uploaded audio files and configuration files in there. You should also download these backups to your computer or upload them to DropBox or JungleDisk for safe keeping.


## Upload OpenVBX

The next step is to get the latest version of OpenVBX from the [OpenVBX GitHub Repository](https://github.com/twilio/OpenVBX). There are two ways to get the new code on to your server:

Upload the archive NEXT to your current install of OpenVBX. Do not overwrite the current install of OpenVBX.


### Via FTP

1. Go to the GitHub repository, click on the "Downloads" button in the upper right. A small popup window will appear. Click on one of the two Download links at the top of popup to download your preferred archive format.
1. Upload the code to the server.
	* If your host allows you to run commands via your FTP client and your FTP client can expand archives then simply upload the archive to your host and expand it there.
	* If you cannot expand the archive on the server then expand it on your local computer and upload the expanded folder to your host. This will be a bit slower, but will work just the same.


### Via curl/wget

If you have shell access to your server then you can bypass FTP and directly download and expand the archive on the server. Download your preferred archive format via `curl` or `wget`. The current production ready version of OpenVBX will always be available at:

* Tarball: [https://github.com/twilio/OpenVBX/tarball/master](https://github.com/twilio/OpenVBX/tarball/master)
* Zip: [https://github.com/twilio/OpenVBX/zipball/master](https://github.com/twilio/OpenVBX/zipball/master)


## Transfer Config & Audio Files

You should now have your old OpenVBX install and your new OpenVBX install sitting next to each other in the same directory. We now need to copy your configuration and audio uploads to the new install.


### Configuration Files

**Copy** the following files from your old install to your new install:

* `OpenVBX/config/database.php`
* `OpenVBX/config/openvbx.php`

If you've modified any other files check them against the files in the new OpenVBX version and copy over your changes to the new versions. Do no completely copy over the old versions as you might overwrite changes in the new files.

If you've modified the following files, copy them to the new install as well. They will overwrite existing files:

* `.htaccess`


### Audio Uploads

**Copy** the files from your old `audio-uploads/` directory to your new `audio-uploads/` directory.


## Permissions

Once you've copied over the necessary files make sure that the `OpenVBX/config/` and `audio-uploads/` directories have the proper permissions. If the directories are not writable by the web server you can adjust the directory permissions using the `chown` and `chmod` commands.


## Go Live

Putting the new version of OpenVBX live is now just a simple matter of renaming your directories. Rename your old version of OpenVBX to a temporary name and rename the new version of OpenVBX to the name that your old one used to have.

Now log in to your install and follow the upgrade instructions (which, really, consists of clicking a few buttons).


## Rollback

Should something go wrong you can roll back your install by reverting the folder names on your OpenVBX install and restoring the database from your backup. If this does happen then please open a ticket on [the Forums](http://getsatisfaction.com/openvbx). 

Be sure to include:

1. Your hosting provider
2. Your PHP version
3. Your MySQL version
4. Version of OpenVBX you are upgrading from and the version that you are upgrading to
