<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, pMachine, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html 
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Code Igniter JSON Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Evan Baliatico
 * @link		http://www.codeigniter.com/wiki/
 */

// ------------------------------------------------------------------------

/* Loading the helper automatically requires and instantiates the Services_JSON class */
if ( ! class_exists('Services_JSON'))
{
	require_once('JSON.php');		
}
$json = new Services_JSON();

/**
 * json_encode
 *
 * Encodes php to JSON code.  Parameter is the data to be encoded.
 *
 * @access	public
 * @param	string
 * @return	string
 */
if(!function_exists('json_encode')) {
	function json_encode($data = null)
	{
		$json = new Services_JSON();
		if($data == null) return false;
		return $json->encode($data);
	}
}
	
// ------------------------------------------------------------------------

/**
 * json_decode
 *
 * Decodes JSON code to php.  Parameter is the data to be decoded.
 *
 * @access	public
 * @param	string
 * @return	string
 */
if(!function_exists('json_decode')) {
	function json_decode($data = null)
	{
		$json = new Services_JSON();
		if($data == null) return false;
		return $json->decode($data);
	}
}
// ------------------------------------------------------------------------

function json_pprint($json)
{

	$tab = "\t";
	$len = strlen($json);
	$pprint_json = '';
	$indent_level = 0;
	$in_string = null;
	
	for($c = 0; $c < $len; $c++)
	{
		$char = $json[$c];
		switch($char)
		{
			case '{':
			case '[':
				if(!$in_string)
				{
					$pprint_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
					$indent_level++;
				}
				else
				{
					$pprint_json .= $char;
				}
			break;
			case '}':
			case ']':
				if(!$in_string)
				{
					$indent_level--;
					$pprint_json .= "\n" . str_repeat($tab, $indent_level) . $char;
				}
				else
				{
					$pprint_json .= $char;
				}
			break;
			case ',':
				if(!$in_string)
				{
					$pprint_json .= ",\n" . str_repeat($tab, $indent_level);
				}
				else
				{
					$pprint_json .= $char;
				}
				break;
			case ':':
				if(!$in_string)
				{
					$pprint_json .= ": ";
				}
				else
				{
					$pprint_json .= $char;
				}
				break;
			case '"':
				$in_string = !$in_string;
			default:
				$pprint_json .= $char;
				break;
		}
	}

	return $pprint_json;
}
?>
