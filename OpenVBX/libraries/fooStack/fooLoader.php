<?php

/*
* fooStack, CIUnit for CodeIgniter
* Copyright (c) 2008-2009 Clemens Gruenberger
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

/*
* CodeIgniter source modified for fooStack / CIUnit
*/

class fooLoader extends CI_Loader {

    var $_ci_loaded_files = array();

    function __construct()
    {
        parent::__construct();
    }

    /**
	 * Load class
	 *
	 * This function loads the requested class.
	 *
	 * @access	private
	 * @param 	string	the item that is being loaded
	 * @param	mixed	any additional parameters
	 * @return 	void
	 */
	function _ci_load_class($class, $params = NULL, $object_name = NULL)
	{
		// Get the class name
		$class = str_replace(EXT, '', $class);
        $prefix = config_item('subclass_prefix');
        $is_fooclass = FALSE;

        //is it in a subfolder?
        $folders = explode('/', $class);
        if (count($folders) > 1)
        {
            $class = array_pop($folders);
            $folders = join('/', $folders).'/';
        }
        else
        {
            $folders = '';
        }
        //print_r($folders);

        // We'll test for both lowercase and capitalized versions of the file name
		foreach (array(ucfirst($class), strtolower($class)) as $class)
		{
			$subclass = APPPATH.'libraries/'.$folders.$prefix.$class.EXT;

			// Is this a class extension request?
			if (file_exists($subclass))
			{
				$baseclass = BASEPATH.'libraries/'.ucfirst($class).EXT;
                if (file_exists(FSPATH.config_item('fooStack_prefix').$class.EXT))
                {
                    require(APPPATH.'libraries/fooStack/foo'.$class.EXT);
                    $is_fooclass = TRUE;
                }

				if ( !file_exists($baseclass) )
				{
					log_message('error', "Unable to load the requested class: ".$class);
					show_error("Unable to load the requested class: ".$class);
				}
                //redesignme, should we load the files?
                //not if another controller before us loaded them
                $include_files = true;

                // Safety:  Was the class already loaded by a previous call?
				if (in_array($subclass, $this->_ci_loaded_files))
				{
                    //unittest, we have to reassign it
                    if ( !defined('CIUnit_Version') )
                    {
                        $is_duplicate = TRUE;
                        log_message('debug', $class." class already loaded. Second attempt ignored.");
                        return;
                    }
                    else
                    {
                        $include_files = false;
                    }
				}
                if ($include_files)
                {
    				include($baseclass);
    				include($subclass);
    				$this->_ci_loaded_files[] = $subclass;
                }

				return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
			}

            // its not an extension request
			// Lets search for the requested library file and load it.
			$is_duplicate = FALSE;
			foreach (array(BASEPATH.'libraries/', APPPATH.'libraries/', FSPATH) as $path)
			{
				$filepath = $path.$folders.$class.EXT;

				// Does the file exist?  No?  Bummer...
				if ( ! file_exists($filepath))
				{
					continue;
				}

                // CI_Unit, should we load the files?
                // not if another controller before us loaded them
                $include_files = true;
				// Safety:  Was the class already loaded by a previous call?
				if (in_array($filepath, $this->_ci_loaded_files))
				{
                    //same thing for main classes
                    //redesignme unittest, we have to reassign it
                    if(!defined('CIUnit_Version'))
                    {
                        $is_duplicate = TRUE;
                        log_message('debug', $class." class already loaded. Second attempt ignored.");
                        return;
                    }
                    else
                    {
                        $include_files = false;
                    }
				}
                if ($include_files)
                {
    				include($filepath);
    				$this->_ci_loaded_files[] = $filepath;
                }
				return $this->_ci_init_class($class, '', $params, $object_name);
			}
		} // END FOREACH

        // Subfolder? (@Mario "Kuroir" Ricalde)
		if ($folders == '')
		{
			$path = strtolower($class).'/'.$class;
			return $this->_ci_load_class($path, $params);
		}


		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if ($is_duplicate == FALSE)
		{
			log_message('error', "Unable to load the requested class: ".$class);
			show_error("Unable to load the requested class: ".$class);
		}
	}

     /**
	 * Instantiates a class
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	null
	 */
	function _ci_init_class($class, $prefix = '', $config = FALSE, $object_name = NULL)
	{
		// Is there an associated config file for this class?
		if ($config === NULL)
		{
			foreach(array(ucfirst($class), strtolower($class)) as $clsName) {
    			if (file_exists(APPPATH.'config/'.$clsName.EXT))
    			{
    				include(APPPATH.'config/'.$clsName.EXT);
    			}
			}
		}

		if ($prefix == '')
		{
			$name = (class_exists('CI_'.$class)) ? 'CI_'.$class : $class;
		}
		else
		{
			$name = $prefix.$class;
		}

		// Set the variable name we will assign the class to
		$class = strtolower($class);
		if (is_null($object_name))
		{
			$classvar = ( ! isset($this->_ci_varmap[$class])) ? $class : $this->_ci_varmap[$class];
		}
		else
		{
			$classvar = $object_name;
		}
		// Instantiate the class
		$CI =& get_instance();
		if ($config !== NULL)
		{
            if (!defined('CIUnit_Version'))
            {
			    $CI->$classvar = new $name($config);
            }
            elseif (!isset($CI->$classvar))
            {
                //redesignme: check if we have got one already..
                $CI->$classvar = new $name($config);
            }
		}
		else
		{
            if (!defined('CIUnit_Version'))
            {
			    $CI->$classvar = new $name;
            }
            elseif (!isset($CI->$classvar))
            {
                //redesignme: check if we have got one already..
                $CI->$classvar = new $name($config);
            }
		}
		
		$this->_ci_classes[$class] = $classvar;
	}
	
	

     /**
	 * Database Loader
	 *
	 * @access	public
	 * @param	string	the DB credentials
	 * @param	bool	whether to return the DB object
	 * @param	bool	whether to enable active record (this allows us to override the config setting)
	 * @return	object
	 */
	function database($params = 'default_test', $return = FALSE, $active_record = FALSE)
	{
        //redesignme, unittest check if there is a DB class already instantiated
        //reuse it if yes
        if (isset($this->_ci_db) and !$return){
            // Grab the super object
		    $CI =& get_instance();
            $CI->db = $this->_ci_db;
        }else{
    		// Do we even need to load the database class?
    		if (class_exists('CI_DB') AND $return == FALSE AND $active_record == FALSE)
    		{
    			return FALSE;
    		}

    		require_once(BASEPATH.'database/DB'.EXT);

            // Load the DB class
            $db =& DB($params, $active_record);

            $my_driver = config_item('subclass_prefix').'DB_'.$db->dbdriver.'_driver';
            $my_driver_file = APPPATH.'libraries/'.$my_driver.EXT;

            if (file_exists($my_driver_file))
            {
                require_once($my_driver_file);
                $db = new $my_driver(get_object_vars($db));
            }

            if ($return === TRUE)
            {
                return $db;
            }
            // Grab the super object
            $CI =& get_instance();

            // Initialize the db variable.  Needed to prevent
            // reference errors with some configurations
            $CI->db = '';
            $CI->db = $db;
            $this->_ci_db =$CI->db;
        }
		// Assign the DB object to any existing models
		$this->_ci_assign_to_models();
	}

    	// --------------------------------------------------------------------

	/**
	 * Autoloader
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, plugins, and helpers to be loaded automatically.
	 *
	 * @access	private
	 * @param	array
	 * @return	void
	 */
	function _ci_autoloader()
	{
        //enable multiple autoload during tests
        include(APPPATH.'config/autoload'.EXT);
		//include_once(APPPATH.'config/autoload'.EXT);

		if ( ! isset($autoload))
		{
			return FALSE;
		}

		// Load any custom config file
		if (count($autoload['config']) > 0)
		{
			$CI =& get_instance();
			foreach ($autoload['config'] as $key => $val)
			{
				$CI->config->load($val);
			}
		}

		// Autoload plugins, helpers and languages
		foreach (array('helper', 'plugin', 'language') as $type)
		{
			if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
			{
				$this->$type($autoload[$type]);
			}
		}

		// A little tweak to remain backward compatible
		// The $autoload['core'] item was deprecated
		if ( ! isset($autoload['libraries']))
		{
			$autoload['libraries'] = $autoload['core'];
		}

		// Load libraries
		if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}

			// Load scaffolding
			if (in_array('scaffolding', $autoload['libraries']))
			{
				$this->scaffolding();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('scaffolding'));
			}

			// Load all other libraries
			foreach ($autoload['libraries'] as $item)
			{
				$this->library($item);
			}
		}

		// Autoload models
		if (isset($autoload['model']))
		{
			$this->model($autoload['model']);
		}

	}
    
   /*
    * Can load a view file from an absolute path and
    * relative to the CodeIgniter index.php file
    * Handy if you have views outside the usual CI views dir
    */
    function viewfile($viewfile, $vars = array(), $return = FALSE)
    {
		return $this->_ci_load(
            array('_ci_path' => $viewfile,
                '_ci_vars' => $this->_ci_object_to_array($vars),
                '_ci_return' => $return)
        );
    }

	// --------------------------------------------------------------------


}

?>