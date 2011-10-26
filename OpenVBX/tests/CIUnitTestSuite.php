<?php

/*
* fooStack, CIUnit for CodeIgniter
* Copyright (c) 2008-2009 Clemens Gruenberger
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

class CIUnitTestSuite {

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

}