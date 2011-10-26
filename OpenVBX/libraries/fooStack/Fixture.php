<?php
/*
* fooStack, CIUnit for CodeIgniter
* Copyright (c) 2008-2009 Clemens Gruenberger
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

/**
* Fixture Class
* loads fixtures
* can be used with CIUnit
*/
class Fixture {

    function __construct()
    {
        //security measure 1: only load if CIUnit is loaded
        if ( !defined('CIUnit_Version') )
        {
            exit('can\'t load fixture library class when not in test mode!');
        }
    }

    /**
    * loads fixture data $fixt into corresponding table
    */
    function load($table, $fixt)
    {
        $this->_assign_db();

        // $fixt is supposed to be an associative array
        // E.g. outputted by spyc from reading a YAML file
        $this->CI->db->simple_query('truncate table ' . $table . ';');

        foreach ( $fixt as $id => $row )
        {
            foreach ($row as $key=>$val)
            {
                if ($val !== '')
                {
                    $row["`$key`"] = $val;
                }
                //unset the rest
                unset($row[$key]);
            }
            $this->CI->db->insert($table, $row);
        }

        $nbr_of_rows = sizeof($fixt);
        log_message('debug',
            "Data fixture for db table '$table' loaded - $nbr_of_rows rows");
    }

    private function _assign_db()
    {
        if ( !isset($this->CI->db) ||
             !isset($this->CI->db->database) )
        {
            $this->CI = &get_instance();
            $this->CI->db = $this->CI->config->item('db');
        }

        //security measure 2: only load if used database ends on '_test'
        $len = strlen($this->CI->db->database);

        if ( substr($this->CI->db->database, $len-5, $len) != '_test' )
        {
            die("\nSorry, the name of your test database must end on '_test'.\n".
                "This prevents deleting important data by accident.\n");
        }
    }

}

