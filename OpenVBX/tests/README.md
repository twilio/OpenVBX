# OpenVBX Integration Tests

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
	$db['default_test']['database'] = 'test';

## Run

	$ cd OpenVBX/tests
	$ phpunit AllTests.php