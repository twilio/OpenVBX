# OpenVBX Change Log

## OpenVBX 1.2.20

- Udpated ReadMe with SSL Certificate requirements.
- Fixed `$cache` variable visibility in `install.php`. (Thanks @gegere)


## OpenVBX 1.2.19

- Fix highlighting of plugin generated pages in sidebar menu. (Thanks @jpwalters)
- Allow Administrator users to "promote" other users to Administrator. (Thanks @jpwalters)
- Allow conferences to be recorded. (Thanks @jpwalters)
- Add programmatic underpinnings to allow for easy recording of dialed calls. (Thanks @jpwalters)
- Doc updates to assist static analysis tools.
- Load Twilio JS via protocol relative url. (Thanks @trenton42)


## OpenVBX 1.2.18

- Fix implementation of several methods by making them static.
- Update config of `base_url` to accommodate servers living behind a proxy.
- Fix validation of the Twilio Request for servers not running mod-rewrite support.
- Fix notification settings save when altering settings as a tenant. (Thanks @AsaadQ)
- Removing obsolete update check.
- Numerous small fixes, code style updates, and docblock fixes of issues found during static analysis.
- Implement cache control on `messages/scripts` endpoint and script tag to prevent caching.
- Fix user edit button href after new users are added to contain the proper user edit url.
- Explicitly hide `E_DEPRECATED` and `E_STRICT` errors in the default error reporting to handle the differences in how different versions of PHP report errors.
- Convert html entities in license section that were causing an email address to be hidden.


## OpenVBX 1.2.17

- Fix implementation of `OpenVBX::connectAuthTenant()` by making it static.
- Fix Text to Speech voice and language picker to properly present and use extended language codes when using the Alice voice.
- Clean up settings forms to redirect back to the same form after submission.
- Fix bug that prevented the population of the Twilio Client Application SID during installation.
- Adding (long overdue) pagination to Numbers screen.


## OpenVBX 1.2.16

- Update SMS Applet to properly use `<Sms>` TwiML when sending messages during voice flows. (Thanks @gegere)


## OpenVBX 1.2.15

- Various small fixes found through static analysis.
- Update verbiage for locating Connect Apps in the Twilio Account Portal. (Thanks @brylie)
- Add endpoint to retrieve list of user ids for a group. (Thanks @joepikowski)
- Update Twilio JS to load version 1.2.
- Fixes to enable international phone number purchasing. The process still requires a re-design but now a persistent admin user can use error messages to navigate the land mines of phone number availability and address requirements.
- Add a local dev helper that replaces `localhost` with `127.0.0.1` to satisfy the Twilio api url validation when configuring phone numbers on a local testing machine.
- Fix GitHub update lookup by adding a user agent string to the GitHub api request.
- Update Text to Speech options to include the Alice voice and Italian voice language options.
- Fix pricing information url in phone number purchase dialog.
- Fix typos (Thanks @RoyHP)
- Remove mention of the iPhone app since it is unfortunately no longer maintained or supported. All functionality is retained, just not advertised.


## OpenVBX 1.2.14

- Fix curl error handling on GitHub API calls.
- Adding SIP validation helper.
- Clean up various innocuous log notices.
- Upgrade Twilio PHP api library. (Thanks @guyhughes)
- Send `busy` calls to voicemail in the dial applet. (Thanks @chadsmith)
- Clarify verbiage in iPhone install guide. (Thanks @plaidfluff)
- Remove obsolete override of `setTimeout` in `global.js` that was causing an untold myriad of issues.
- Update `VBX_Github_Client` to use new PHP 5.5 goodness when available.
- Rework template loading to allow for ajax-loading of chrome-less templates.
- Update SMS message sending to use new `/Messages` endpoint to enable 1600 character message lengths. Individual plugins will need to update on their own to take advantage of the extra available message length.
- Fix setting of modal global default options.


## OpenVBX 1.2.13

- Send proper JSON header during install steps.
- Fix bug in Twilio Services usage where custom certificate would not load.


## OpenVBX 1.2.12

- Fix cache table `value` field to be `mediumblob` instead of `text`. Fixes an issue where large lists of phone numbers (300+) would overflow the value field and corrupt the cached data.
- Fix exception with latest versions of PHP when a default timezone is not set.
- Fix Github API request for upgrade version check to use Github API V3.
- Removing Sandbox number access. Sandbox numbers are no longer supported by Twilio.
	- Also fixes an issue when loading phone number lists where looking for the pin attribute would cause a series of unecessary api calls.
- Updating Twilio PHP library to fix issue with special characters in TwiML output.
- Fix issue with double-encoded entities that manifested in flow editor. Use the `double_encode` flag in `htmlspecialchars` to protect against it happening.
    - Minimum PHP version bumped to 5.2.3 to accommodate the double encode flag.


## OpenVBX 1.2.11

- Fix character counting on message detail page. Props to @walker.
- Add focus to inputs on certain dialogs and pickers. Props to @walker.
- Bump Twilio JS to 1.1 for WebRTC support.


## OpenVBX 1.2.10

- Fix improper exception pass through that would show the wrong error message to a user when updating settings.
- Adding a favicon to help keep server logs quiet.
- Fix to timing applet to properly handle wrapping around to Sunday.
- Fix to conference applet to properly set the hasModerator flag and not allow just anyone to start a conference.
- Disable sandbox display by default. Sandbox is now deprecated by Twilio on all new accounts.
- Remove call to `uniqid` in conference applet `ui.php` since `uniqid` seems to have issues on different systems. Replaced with call to `mt_rand`.
- Fix request validation where url rewriting is enabled but the `vbxsite` variable still appears in the request uri parameter.
- Fix unnecessary failure when trying to install on `localhost`.
- Fix for emails not containing the properly adjusted message time.
- Fix device call sequence to respect the sequence when dialing a user.
- Trust Twilio REST Api objects when scrubbing data.
- Add sample plugin for Twilio Usage API data.


## OpenVBX 1.2.9

- Reverted previous change made in 1.2.8 that allowed for GET params in request validations when using pretty urls.


## OpenVBX 1.2.8

- Fixed TwimlDial class to properly set the dial timeout as passed in. Fixes an issue where the system settings dial timeout value wouldn't apply to the dial applet.
- No longer using VBX settings for rewrite to allow or deny GET params in the request validation to support GET as well as POST methods on incoming TwiML requests. Props to @fomojola.
- Fixed an issue where a server that doesn't support the GitHub library for version checking would also hide the Tenants Settings tab.


## OpenVBX 1.2.7

- remove reference to $30 credit when signing up for a Twilio trial accounts


## OpenVBX 1.2.6

- properly formulate gravatar urls when using https
- properly handle exceptions when calling GitHub for latest tag data during version check
- include option to use Twilio SSL Certificate when making api requests. Fixes issue on some hosts where curl certs are out of date
- add pagination to Flows screen. Previous limit was 100 flows displayed on a single screen
- added proper asset url versioning to iframe assets


## OpenVBX 1.2.5

- add `is_numeric` to id check during model save since PHP likes to do weird intval conversions
- fix log error message when looking for a controller when the base url is being loaded
- fix error with tenant login when the tenant name was saved with capital letters in it. Tenants are now redirected to their correct, proper-case url
- load special error page instead of showing white-screen when running on PHP4
- fix Settings > Twilio Account view for Connect tenants since the Token is not required
- fix an issue with passing boolean values to Services Twilio where string literal 'true' and 'false' should be used instead
- fix an issue that could prevent the dial timeout from being properly applied
- explicitly setting the `timeLimit` value on the conference dial to the stated default of 4 hours as a user has reported being cut-off at 1 hour
- adding check for `safe_mode` and `open_basedir` restrictions as they disallow the setting of `CURLOPT_FOLLOWLOCATION` (and kills the GitHub tag request for the upgrade check)
- cleaned up license section of the Settings > About screen


## OpenVBX 1.2.4

- fix Connect app authorization return to properly authorize the returning user
- merge pull request to make whisper on outbound dialing via the Dial applet optional


## OpenVBX 1.2.3

- fix scoping bug on PHP 5.2 based servers that resulted from previous PluginData cache fix


## OpenVBX 1.2.2

- fix number assignment to flow to null ApplicationSid fields as they always trump url values and can prevent a number assignment from taking hold
- fix memcache cache generation method to properly query the objects cache generation
- fix `VBX_Plugin_Store` to return a generated `id` field when requested. The PluginStore database table doesn't have a primary key of `id` like other cached database data so we need to generate one on a per request basis to enable proper caching of this data.
- removed a check for php versions < 5.2 in MY_Model


## OpenVBX 1.2.1

- update outgoing email to properly set `user-agent`, `reply-to` and `return-path` for sent emails
	- `return-path` won't always override depending upon the server's sendmail configuration
- fix cache table keys to fix rare condition where mysql would reserve key-name lengths for utf8 making the resulting key length reservation more than 1000 characters
- add minification config override for servers that don't define a proper server document root
- fix install setup error return to properly display the error


## OpenVBX 1.2

- redesign numbers screen to separate assigned, unassigned, and numbers in-use by other systems
- change user edit screens to always use dedicated page instead of a popup
- add [object caching layer](https://github.com/twilio/OpenVBX/wiki/Object-Cache) supporting the following mechanisms:
	- local (per page load memory cache)
	- Database
	- APC
	- Memcache
- add API cache that uses the database to cache API results from Twilio
- add method to flush caches via About screen
- show cache object info in About screen
- clean most models for code consistency & cache efficiency
- move user `last_seen`, `online` & `last_login` to be user settings
- add helpful data in user view screen to help admins to inspect users
- fix password length bug, set min-length to 8 characters
- upgrade password storage security using [phppass](http://www.openwall.com/phpass/)
- add helpful documentation snippets in the Settings screens
- add better error messages from failed API transactions
- setting version number in a file instead of from database
	- allows static operations to properly set an appropriate api user agent
- changing `Services_Twilio` user agent to identify itself as OpenVBX
- increased test coverage for applets, implement data fixtures, and include better test documentation
- change behavior of TwiML preview links in flow editor to open in a new window
- add system setting for controlling the system time zone
- add system setting for controlling email notifications for new messages
- add system setting for controlling the display of the Sandbox number (parent tenant only)
- add system setting for controlling the duration of the dail timeout
	- setting is global
- add upgrade notice functionality to Admin section
	- automatically detects new tag versions on GitHub and displays a banner to admins
- fix connect app sid validation during install
- fix an uncaught exception when trying to send voicemail sms notifications from a non-sms enabled number
- added check during voicemail notification to not attempt to send SMS notification if the incoming number is not sms enabled
	- in the future this should fall back to another number that is designated as a fallback SMS notification number.
- general html & css cleanup
- fix for servers running on non-standard ports so that manually changing the `$config['cooke_path']` is no longer required to be able to log in
- change .htaccess file's `mod_deflate` directives to exclude SWF files
- CSS updates to bring modern versions of IE visually up to par with other browsers


## OpenVBX 1.1.3

- fix an issue with call to record where the caller id field is missing and causes the call to fail
- fix an issue with Client account creation where `rewrite_enabled` isn't considered when constructing the url
- fixes issues with following external URLs in to the application
- fixes issue querying for incoming numbers on accounts with no sandbox
- fix an unhandled exception when accessing a message that doesn't exist
- update & add user notices around non-existent and disallowed message view attempts
- add notices on Twilio Account screen in the event of malformed Client Application data
- update steps.js to change order of "next" and "submit" buttons instead of overriding the form submit. Using the order of buttons to designate the primary action makes the event firing more predictable across browsers
- add email address validation to install process
- add ability to go to previous install step on last step of install
- add attempt during install to recreate the `.htaccess` file if it is not present
- fixes a request validation error when dialing using a device and when `mod_rewrite` is not enabled
- add link to Troubleshooting/Common Issues page on GitHub
- fixes an errant check on tenant when checking if tenant is using Twilio Connect
- apply consistent use of `asset_url()`
- fixed error that would cause System Config update to cause an Application update with empty urls


## OpenVBX 1.1.2

- fixed issue with normal numbers in Dial applet
- attempt to fix issue with a rare logout redirect that lands a sub-tenant user on the parent-tenant's login page


## OpenVBX 1.1.1

- fixed an issue with validation of requests from Twilio
- fixed an issue with gathering devices during a group dial
- fixing issue with media uploading when a user's email contains a "+"
- silenced various notices
- added more system status output to Settings > About
- fixed possible tenant first run sending to Connect signup when not necessary


## OpenVBX 1.1

- integrate [Twilio Connect](http://www.twilio.com/docs/connect/) for Tenants
- adding voice & language preferences to site options to allow administrator to set site wide options for the voice type and language of voice
	- enable tenant administrators to edit their system settings
- adding transcription preference to site options to allow transcriptions to be turned off
- adding ability to sort users in a group
- updated Twilio Client UI slider to fully integrate dialing functionality in to slider dialog
	- dialog now saves state as user specific settings
	- dialog allows dialing using any configured device wether active or not
	- dialog shows a list of users in the system along with a quick dial button
	- dialog live updates when user/device/number data changes in the system
		- for user making the change, other users currently require logging out/in to refresh
- adding [Minify.php](http://code.google.com/p/minify/) for asset minification in lieu of pre-compiled assets
- enable the purchase of numbers by specific country
- display and enforce number capabilities. not all numbers are allowed to use sms
- updated API library to latest [Services Twilio library](https://github.com/twilio/twilio-php) & updated all calls to Twilio to use the new library
	- modify internal calls & standard applets to use new library for API calls & TwiML generation
- updated install, upgrade & welcome JS to abstract out the steps slider
- updated install routine to detect & use mysqli if available
- added native sequential="true" attribute to Dial actions
- hardened install/upgrade error handling JavaScript
- showing number names in Twilio numbers list
	- numbers are named in the Twilio Account portal
- added form convenience helpers to Twilio Helper
- added Upgrade instructions file
- added user settings table
	- added user `setting()` and `setting_set()` interfaces on VBX_User
	- added /account/settings ajax endpoint for setting preferences
- improved Twilio Client init & user online status handling when Client takes a while to init
- added deprecation notices to old api library items
- added new Twilio helper file with:
	- request validation
	- clean digits helper
	- url versioning helper
	- deprecation notice helper
- documentation content and formatting updates
- bumped system PHP requirement to 5.2+
	- PHP versions less than 5.2 are no longer supported
- better handle disabled tenants during upgrade
- added integration testing library based on [foostack](http://www.foostack.com/foostack/)
	- started integration tests for applets
- fixing database debug setting default. Now defaults to off
- added [Gravatar](http://gravatar.com) support in theme options
- users list is now ordered by user last name & administrators are identified in the list
- adding local config override option for `OpenVBX/config/config.php`
	- overrides are ignored via .gitignore
- fix for audio uploads 302 error
- fix bug in rest access tokens that could allow unauthorized access to twiml controller
	- each twilio client outbound call now has its own rest access token
- add more standards compliant CSS for base UI styling
- fixed bug where an error entering basic tenant info could create an incomplete tenant
- added Changelog file


## OpenVBX 1.0.5

- fixed issue with validation of `rest_access` tokens
- modified Twilio Client interface to request a new `rest_access` token for each call
- fixed iPhone install url helper
	
	
## OpenVBX 1.0.4

- changed cookie tracking in dial applet to use the built in CI session instead 
- fixed issue with browser phone when the client connection takes a while to become ready
- fixed online status indicator for new users
- added better messaging during install if server tests do not pass
- added progress indicator in submit button during upgrade
- fix double encoding bug with output sanitization
- fix to properly use `recording_host` if provided
- fix for OpenVBX running on nginx servers
- fix to properly persist session data on twiml delivery
- minor fixes, updates, and JS nicities


## OpenVBX 1.0.3

- Fixed infinite subtenant redirect


## OpenVBX 1.0.2

- Fixed bad behavior if flash is not Installed or if a Flash blocker is in use
- Fixed flow editor & possible flow failure if `date.default_timezone` is not defined in PHP for the timing Applet
- Fixed issue with handling normal numbers in Dial applet
- Allow dismissal of the Browser Phone slider in case of an error


## OpenVBX 1.0.1

- Handling browser not having flash or a using a flash-blocker


## OpenVBX 1.0

- Twilio Client Support
- Site now runs in an iFrame to provide persistent call support to Twilio Client calls
- jQuery 1.6.2 & jQuery UI 1.8.14
  - jQuery 1.6 made some important changes. More information about what changed with jQuery 1.6 can be found here: http://blog.jquery.com/2011/05/03/jquery-16-released/
  - upgraded Soundmanager2 plugin & now require Flash 9
- Added plugin page titles
- Added the ability to rename flows
- Fix incoming numbers query to properly pull all incoming numbers for an account
- Fixed bug in Dial applet that would cause an infinite dial loop
- Fixed bug in Menu applet that would cause it not to save and/or damage data
- Removed iPhone app banner
- Normalized `OpenVBX.home` & `OpenVBX.assets` in site wide JS to NOT have a trailing slash
- Fixed bug that prohibited the use of OpenVBX with database table prefixes
- Many bug fixes
- Updated repository layout to use Git Flow principles
  - learn more about git-flow here: https://github.com/nvie/gitflow


## OpenVBX 0.91

- fix case issue on timezone
- add 3rd party plugin notice to upgrade routine
- add trademark info to README file
- added GoDaddy specific install instructions
  

## OpenVBX 0.90

- Implemented OpenVBX 2010 Number Upgrader into standard upgrade system
- Added support for arbitrary php scripts in upgrade process.
- Added basic auth support for CGI based installations
- UX Integration of OpenVBX for iPhone into OpenVBX message and installation workflow
   - Added invitation support for OpenVBX iphone app
   - Added new external linking support with browser detection for opening details in iphone app
- New 2010 number provisioning for tollfree and domestic numbers
- Fix for Error when changing the phone number on an existing user
- Added restriction for OpenVBX iphone app to 0.90	
- Fix for user password reset in OpenVBX
- For for problem with URLs containing double slashes in url generator
