<?php

/*
* fooStack, CIUnit for CodeIgniter
* Copyright (c) 2008-2009 Clemens Gruenberger
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

define('FSPATH', APPPATH.'libraries/fooStack/');

function foo_config()
{
    $config['foostack']['prefix'] = 'foo';
    $config['foostack']['active_plugins']= array('fooStack','ME');
    return $config['foostack'];
}