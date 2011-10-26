<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2009, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
* Determines if the current version of PHP is greater then the supplied value
*
* Since there are a few places where we conditionally test for PHP > 5
* we'll set a static variable.
*
* @access	public
* @param	string
* @return	bool
*/
function is_php($version = '5.0.0')
{
	static $_is_php;
	$version = (string)$version;
	
	if ( ! isset($_is_php[$version]))
	{
		$_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? FALSE : TRUE;
	}

	return $_is_php[$version];
}
// FooStack CiUnit Add:
include(dirname(__FILE__) . '/config.php');
// ------------------------------------------------------------------------

/**
 * Tests for file writability
 *
 * is_writable() returns TRUE on Windows servers when you really can't write to 
 * the file, based on the read-only attribute.  is_writable() is also unreliable
 * on Unix servers if safe_mode is on. 
 *
 * @access	private
 * @return	void
 */
function is_really_writable($file)
{	
	// If we're on a Unix server with safe_mode off we call is_writable
	if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == FALSE)
	{
		return is_writable($file);
	}

	// For windows servers and safe_mode "on" installations we'll actually
	// write a file then read it.  Bah...
	if (is_dir($file))
	{
		$file = rtrim($file, '/').'/'.md5(rand(1,100));

		if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
		{
			return FALSE;
		}

		fclose($fp);
		@chmod($file, DIR_WRITE_MODE);
		@unlink($file);
		return TRUE;
	}
	elseif (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
	{
		return FALSE;
	}

	fclose($fp);
	return TRUE;
}

// ------------------------------------------------------------------------

/**
* Class registry
*
* This function acts as a singleton.  If the requested class does not
* exist it is instantiated and set to a static variable.  If it has
* previously been instantiated the variable is returned.
*
* @access	public
* @param	string	the class name being requested
* @param	bool	optional flag that lets classes get loaded but not instantiated
* @return	object
*/
function &load_class($class, $instantiate = TRUE)
{
	static $objects = array();

    /* fooStack CIUnit Add - Start */
    $prefix = config_item('subclass_prefix');
    $fs = foo_config();
    $fooprefix = $fs['prefix'];

    $is_fooclass = FALSE;

    //is it in a subfolder?
    $folders = explode('/', $class);
    if(count($folders) > 1){
          $class = array_pop($folders);
          $folders = join('/', $folders).'/';
    }else{
        $folders = '';
    }
    //echo "folder:".print_r($folders,1);
    //echo "class:".print_r($class,1)." *\n";
    /* fooStack CIUnit Add - End */


	// Does the class exist?  If so, we're done...
	if (isset($objects[$class]))
	{
		return $objects[$class];
	}

	// user extension class?
	if (file_exists(APPPATH.'libraries/'.$folders.$prefix.$class.EXT))
	{
		require(BASEPATH.'libraries/'.$class.EXT);

        /* fooStack CIUnit Add - start */
        // extend with fooStack class
        if(file_exists(FSPATH.$fooprefix.$class.EXT)){
            require(FSPATH.$fooprefix.$class.EXT);
            $is_fooclass = TRUE;
        }
        /* fooStack CIUnit Add - End */

        // load user class
		require(APPPATH.'libraries/'.$folders.$prefix.$class.EXT);
		$is_subclass = TRUE;
	}
	else
	{
        // independent user class?
        if (file_exists(APPPATH.'libraries/'.$folders.$class.EXT))
		{
            //load it
			require(APPPATH.'libraries/'.$folders.$class.EXT);
			$is_subclass = FALSE;
            /* fooStack CIUnit Add - start */
            $is_fooclass = FALSE;
            /* fooStack CIUnit Add - end */
		}
		else
		{
            // so it must be a base class / or foostack class
			require(BASEPATH.'libraries/'.$class.EXT);
            /* fooStack CIUnit Add - start */
            if(file_exists(FSPATH.$fooprefix.$class.EXT)){
                require(FSPATH.$fooprefix.$class.EXT);
                $is_fooclass = TRUE;
            }
            /* fooStack CIUnit Add - end */
			$is_subclass = FALSE;
		}
	}

	if ($instantiate == FALSE)
	{
		$objects[$class] = TRUE;
		return $objects[$class];
	}

	if ($is_subclass == TRUE)
	{
		$name = $prefix.$class;

		$objects[$class] =& instantiate_class(new $name());
		return $objects[$class];
	}

    $prefix = $is_fooclass ? $fooprefix : 'CI_';

    //is there a class of this name? if not, add prefix
	$name = (class_exists($class)) ? $class : $prefix.$class;

	$objects[$class] =& instantiate_class(new $name());
	return $objects[$class];
}

/**
 * Instantiate Class
 *
 * Returns a new class object by reference, used by load_class() and the DB class.
 * Required to retain PHP 4 compatibility and also not make PHP 5.3 cry.
 *
 * Use: $obj =& instantiate_class(new Foo());
 * 
 * @access	public
 * @param	object
 * @return	object
 */
function &instantiate_class(&$class_object)
{
	return $class_object;
}


/**
* fooStack lets plugins each extend their own CI libraries.
* in order to be able to do that we have to find out what files are to be included
* and what objects have to be intermediary
*/
function find_load_order($class){

    //user prefix
    $prefix = config_item('subclass_prefix');

    //foostack config var
    $fs = foo_config();

    //foostack end user class prefix - foostack classes can be exended by the user
    $fooprefix = $fs['prefix'];

    //main cases:

    //base classes - fooextension classes - fooadditional classes - user classes - user extension classes

    // load base class (fooconfig decideds if we extend it)
    // load non-base class, either fooclass


    $is_fooclass = FALSE;

    //is it in a subfolder?
    $folders = explode('/', $class);
    if(count($folders) > 1){
          $class = array_pop($folders);
          $folders = join('/', $folders).'/';
    }else{
        $folders = '';
    }
    //echo "folder:".print_r($folders,1);
    //echo "class:".print_r($class,1)." *\n";



	// Does the class exist?  If so, we're done...
	if (isset($objects[$class]))
	{
		return $objects[$class];
	}

	// user extension class?
	if (file_exists(APPPATH.'libraries/'.$folders.$prefix.$class.EXT))
	{
        // require Base Class
		require(BASEPATH.'libraries/'.$class.EXT);


        // extend with fooStack class
        if(file_exists(FSPATH.$fooprefix.$class.EXT)){
            require(FSPATH.$fooprefix.$class.EXT);
            $is_fooclass = TRUE;
        }

        // load user class
		require(APPPATH.'libraries/'.$folders.$prefix.$class.EXT);
		$is_subclass = TRUE;
	}

	else
    {
        // independent user class?
        if (file_exists(APPPATH.'libraries/'.$folders.$class.EXT))
		{
            //load it
			require(APPPATH.'libraries/'.$folders.$class.EXT);
			$is_subclass = FALSE;
            $is_fooclass = FALSE;
		}
		else
		{
            // so it must be a base class / or foostack class
			require(BASEPATH.'libraries/'.$class.EXT);
            if(file_exists(FSPATH.$fooprefix.$class.EXT)){
                require(FSPATH.$fooprefix.$class.EXT);
                $is_fooclass = TRUE;
            }
			$is_subclass = FALSE;
		}
	}

	if ($instantiate == FALSE)
	{
		$objects[$class] = TRUE;
		return $objects[$class];
	}

	if ($is_subclass == TRUE)
	{
		$name = $prefix.$class;
        echo "Subclass ". $name . " loading..";
		$objects[$class] =& instantiate_class(new $name());
		return $objects[$class];
	}

    $prefix = $is_fooclass? $fooprefix:'CI_';
	$name = ($class != 'Controller') ? $prefix.$class : $class;

    //echo "Class ". $name. " loading..";

	$objects[$class] =& instantiate_class(new $name());
	return $objects[$class];

}

/**
* Loads the main config.php file
*
* @access	private
* @return	array
*/
function &get_config()
{
	static $main_conf;

	if ( ! isset($main_conf))
	{
		if ( ! file_exists(APPPATH.'config/config'.EXT))
		{
			exit('The configuration file config'.EXT.' does not exist.');
		}

		require(APPPATH.'config/config'.EXT);

		if ( ! isset($config) OR ! is_array($config))
		{
			exit('Your config file does not appear to be formatted correctly.');
		}

		$main_conf[0] =& $config;
	}
	return $main_conf[0];
}

/**
* Gets a config item
*
* @access	public
* @return	mixed
*/
function config_item($item)
{
	static $config_item = array();

	if ( ! isset($config_item[$item]))
	{
		$config =& get_config();

		if ( ! isset($config[$item]))
		{
			return FALSE;
		}
		$config_item[$item] = $config[$item];
	}

	return $config_item[$item];
}


/**
* Error Handler
*
* This function lets us invoke the exception class and
* display errors using the standard error template located
* in application/errors/errors.php
* This function will send the error page directly to the
* browser and exit.
*
* @access	public
* @return	void
*/
function show_error($message, $status_code = 500)
{
	$error =& load_class('Exceptions');
	echo $error->show_error('An Error Was Encountered', $message, 'error_general', $status_code);
	exit;
}


/**
* 404 Page Handler
*
* This function is similar to the show_error() function above
* However, instead of the standard error template it displays
* 404 errors.
*
* @access	public
* @return	void
*/
function show_404($page = '')
{
	$error =& load_class('Exceptions');
	$error->show_404($page);
	exit;
}


/**
* Error Logging Interface
*
* We use this as a simple mechanism to access the logging
* class and send messages to be logged.
*
* @access	public
* @return	void
*/
function log_message($level = 'error', $message, $php_error = FALSE)
{
	static $LOG;
	
	$config =& get_config();
	if ($config['log_threshold'] == 0)
	{
		return;
	}

	$LOG =& load_class('Log');
	$LOG->write_log($level, $message, $php_error);
}


/**
 * Set HTTP Status Header
 *
 * @access	public
 * @param	int 	the status code
 * @param	string	
 * @return	void
 */
function set_status_header($code = 200, $text = '')
{
	$stati = array(
						200	=> 'OK',
						201	=> 'Created',
						202	=> 'Accepted',
						203	=> 'Non-Authoritative Information',
						204	=> 'No Content',
						205	=> 'Reset Content',
						206	=> 'Partial Content',

						300	=> 'Multiple Choices',
						301	=> 'Moved Permanently',
						302	=> 'Found',
						304	=> 'Not Modified',
						305	=> 'Use Proxy',
						307	=> 'Temporary Redirect',

						400	=> 'Bad Request',
						401	=> 'Unauthorized',
						403	=> 'Forbidden',
						404	=> 'Not Found',
						405	=> 'Method Not Allowed',
						406	=> 'Not Acceptable',
						407	=> 'Proxy Authentication Required',
						408	=> 'Request Timeout',
						409	=> 'Conflict',
						410	=> 'Gone',
						411	=> 'Length Required',
						412	=> 'Precondition Failed',
						413	=> 'Request Entity Too Large',
						414	=> 'Request-URI Too Long',
						415	=> 'Unsupported Media Type',
						416	=> 'Requested Range Not Satisfiable',
						417	=> 'Expectation Failed',

						500	=> 'Internal Server Error',
						501	=> 'Not Implemented',
						502	=> 'Bad Gateway',
						503	=> 'Service Unavailable',
						504	=> 'Gateway Timeout',
						505	=> 'HTTP Version Not Supported'
					);

	if ($code == '' OR ! is_numeric($code))
	{
		show_error('Status codes must be numeric', 500);
	}

	if (isset($stati[$code]) AND $text == '')
	{				
		$text = $stati[$code];
	}
	
	if ($text == '')
	{
		show_error('No status text available.  Please check your status code number or supply your own message text.', 500);
	}
	
	$server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;

	if (substr(php_sapi_name(), 0, 3) == 'cgi')
	{
		header("Status: {$code} {$text}", TRUE);
	}
	elseif ($server_protocol == 'HTTP/1.1' OR $server_protocol == 'HTTP/1.0')
	{
		header($server_protocol." {$code} {$text}", TRUE, $code);
	}
	else
	{
		header("HTTP/1.1 {$code} {$text}", TRUE, $code);
	}
}


/**
* Exception Handler
*
* This is the custom exception handler that is declaired at the top
* of Codeigniter.php.  The main reason we use this is permit
* PHP errors to be logged in our own log files since we may
* not have access to server logs. Since this function
* effectively intercepts PHP errors, however, we also need
* to display errors based on the current error_reporting level.
* We do that with the use of a PHP error template.
*
* @access	private
* @return	void
*/
function _exception_handler($severity, $message, $filepath, $line)
{	
	 // We don't bother with "strict" notices since they will fill up
	 // the log file with information that isn't normally very
	 // helpful.  For example, if you are running PHP 5 and you
	 // use version 4 style class functions (without prefixes
	 // like "public", "private", etc.) you'll get notices telling
	 // you that these have been deprecated.
	
	if ($severity == E_STRICT)
	{
		return;
	}

	$error =& load_class('Exceptions');

	// Should we display the error?
	// We'll get the current error_reporting level and add its bits
	// with the severity bits to find out.
	
	if (($severity & error_reporting()) == $severity)
	{
		$error->show_php_error($severity, $message, $filepath, $line);
	}
	
	// Should we log the error?  No?  We're done...
	$config =& get_config();
	if ($config['log_threshold'] == 0)
	{
		return;
	}

	$error->log_exception($severity, $message, $filepath, $line);
}



/* End of file Common.php */
/* Location: ./system/codeigniter/Common.php */