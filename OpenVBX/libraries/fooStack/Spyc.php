<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$libraryDir = APPPATH . 'libraries/fooStack/Spyc';

if (!is_dir($libraryDir))
    exit("Spyc must be located in \"$libraryDir\"");

require_once($libraryDir . '/spyc.php');

/**
* Spyc 1.0 Support for CodeIgniter
* CodeIgniter-library bridge for the Spyc YAML library, http://spyc.sourceforge.net/
*
* See spyc/README for more info
*/

