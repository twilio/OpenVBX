# OpenVBX Integration Tests

Test coverage is still woefully inadequate, but it is getting better.


## Configure Database Settings

Set up the database to be tested in `OpenVBX/config/database.php`. Add another config for `default_test`. For example, to use all the same settings, to change the database name:
	
	<?php
	
	$active_group = 'default';
	$active_record = TRUE;
	$db['default']['username'] = 'root';
	$db['default']['password'] = '';
	$db['default']['hostname'] = 'localhost';
	$db['default']['database'] = 'OpenVBX';
	$db['default']['dbdriver'] = 'mysqli';
	$db['default']['dbprefix'] = '';
	$db['default']['pconnect'] = FALSE;
	$db['default']['db_debug'] = FALSE;
	$db['default']['cache_on'] = FALSE;
	$db['default']['cachedir'] = '';
	$db['default']['char_set'] = 'utf8';
	$db['default']['dbcollat'] = 'utf8_general_ci';

	$db['default_test'] = $db['default'];
	$db['default_test']['database'] = 'openvbx_test';

**The test database name must end in `_test` to comply with the Fixture loading.** The fixtures will complain and tests will fail if you don't use this convention.

The `$active_group` is automatically selected in the `fooLoader` class & in the Fixtures processes.


## Configure Twilio Sid & Twilio Token

The Tests are configured to pull a default Sid & Token from a system environment variable. This has been tested on *NIX but not on Windows. Any help is getting this to be cross platform would be appreciated.


### Unix Sid & Token Setup

To automatically load a system ENV variable set up your `.bash_profile` with your Sid & Token values.

	export TWILIO_SID="AC123..."
	export TWILIO_TOKEN="123..."


### Windows Sid & Token Setup

TBD - If you can help in this area then by all means submit a pull request!


## Test Data & Fixtures

The database is set up with the standard OpenVBX.sql file that is used to install OpenVBX for normal use. For each test run for OpenVBX, tests that extend the `OpenVBX_TestCase` class, a base amount of information is automatically populated to the database for each test iteration from the fixtures directory.

Data that is populated includes:

- 3 test users
- 3 test devices (1 for each user)
- Users 2 & 3 are associated to Group ID 2
- Some inconsequential (at the time of this writing) user settings are logged


## Run

	$ cd OpenVBX/tests
	$ phpunit AllTests.php