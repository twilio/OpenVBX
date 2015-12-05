<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 **/

class MY_Router extends CI_Router
{
	var $default_method = 'index';
	var $tenant = null;
	var $api_version;
	
	function _validate_tenant($segments)
	{
		$tenant = $segments[0];
		$controller = isset($segments[1])? $segments[1] : null;
		
		if(is_string($tenant)
		   && in_array($tenant, array_keys($this->routes)))
		{
			return false;
		}

		if($controller &&
		   !in_array($controller, array_keys($this->routes)))
		{
			return false;
		}
		
		if(is_null($controller)
		   && !empty($tenant))
		{
			return $segments;
		}

		
		return $segments;
	}

	function _validate_api($segments)
	{
		$api_prefix = isset($segments[0])? $segments[0] : null;
		$api_version = isset($segments[1])? $segments[1] : null;
		
		if(!$api_prefix)
		{
			return false;
		}

		if($api_prefix != 'api')
		{
			return false;
		}
		
		switch($api_version)
		{
			case '2009-12-01':
				break;
			default:
				return false;
		}
		return true;
	}

	function _validate_tenant_api($segments)
	{
		$tenant = isset($segments[0])? $segments[0] : null;
		$api_prefix = isset($segments[1])? $segments[1] : null;
		$api_version = isset($segments[2])? $segments[2] : null;
		$controller = isset($segments[3])? $segments[3] : null;
		
		if(!$api_prefix)
		{
			return false;
		}

		if($api_prefix != 'api')
		{
			return false;
		}
		
		switch($api_version)
		{
			case '2009-12-01':
				break;
			default:
				return false;
		}

		if(is_string($tenant)
		   && in_array($tenant, array_keys($this->routes)))
		{
			return false;
		}

		if($controller &&
		   !in_array($controller, array_keys($this->routes)))
		{
			return false;
		}
		
		return true;
	}

	function set_tenant($tenant)
	{
		$this->tenant = $tenant;
	}

	function set_api_version($api_version)
	{
		$this->api_version = $api_version;
	}

	function _get_relative_segments($segments)
	{
		if (file_exists(APPPATH.'controllers/'.$segments[0].EXT))
		{
			return $segments;
		}

		// Is the controller in a sub-folder?
		if (is_dir(APPPATH.'controllers/'.$segments[0]))
		{
			// Set the directory and remove it from the segment array
			$this->set_directory($segments[0]);
			
			$segments = array_slice($segments, 1);
			
			if (count($segments) > 0 &&
				file_exists(APPPATH.'controllers/'.$this->directory
							.'/'.$segments[0].EXT))
			{
				return $segments;
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method($this->default_method);
				
				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.EXT))
				{
					$this->directory = '';
					return array();
				}
			
			}
			
			return $segments;
		}

		return false;
	}

	/**
	 * @param $segments
	 * @return array
	 */
	function _validate_request($segments)
	{
		// Does the requested controller exist in the root folder?
		if (!empty($segments[0]) && file_exists(APPPATH.'controllers/'.$segments[0].EXT))
		{
			return $segments;
		}

		// Is the controller in a sub-folder?
		$segment = (!empty($segments[0])) ? $segments[0] : null;
		if (is_dir(APPPATH.'controllers/'.$segment))
		{		
			// Set the directory and remove it from the segment array
			$this->set_directory($segment);
			$segments = array_slice($segments, 1);
			
			if (count($segments) > 0)
			{
				// Does the requested controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].EXT))
				{
					show_404($this->fetch_directory().$segments[0]);
				}
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');
			
				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.EXT))
				{
					$this->directory = '';
					return array();
				}
			
			}

			return $segments;
		}

		// Can't find the requested controller...
		show_404($segment);
	}

	/**
	 * @deprecated
	 * @param $rel_segments
	 * @param $segments
	 * @return array
	 */
	private function _parse_segments($rel_segments, $segments) 
	{
		$new_segments = array(
							  $rel_segments[0]
							  );
				
		if(isset($rel_segments[1])
		   && ($rel_segments[1] > 0
			   || $rel_segments[1] == '0')
		   )
		{
			$new_segments[] = $this->default_method;
			$new_segments[] = $rel_segments[1];
					
			if(count($rel_segments) > 2)
			{
				foreach(array_slice($segments, 3) as $seg)
				{
					$new_segments[] = $seg;
				}
			}
			$this->set_method($this->default_method);
		}
		else
		{
			if(isset($rel_segments[1]))
			{
				$new_segments[] = $rel_segments[1];
			}
			
			foreach(array_slice($rel_segments, 2) as $seg)
			{
				$new_segments[] = $seg;
			}
		}
		
		$rel_segments = $new_segments;
		
		return $rel_segments;		 
	}

	function _parse_routes()
	{
		// Do we even have any custom routing to deal with?
		// There is a default scaffolding trigger, so we'll look just for 1
		if (count($this->routes) == 1)
		{
			$this->_set_request($this->uri->segments);
			return;
		}
		
		$segments = $this->uri->segments;
		if($this->_validate_api($segments))
		{
			$api = $segments[0];
			$api_version = $segments[1];
			$segments = array_slice($segments, 2);
			$this->set_api_version($api_version);
			$this->uri->segments = $segments;
		}

		if(($tenant_segments = $this->_validate_tenant($segments)))
		{
			$tenant = $segments[0];
			$segments = array_slice($segments, 1);
			$this->set_tenant($tenant);
			if(empty($segments))
			{
				$this->set_directory('iframe');
				$this->uri->segments = array();
			}
			else
			{
				$this->uri->segments = $segments;
			}
		}
		
		if($this->_validate_tenant_api($segments))
		{
			$tenant = $segments[0];
			$api = $segments[1];
			$api_version = $segments[2];
			$this->set_tenant($tenant);
			$this->set_api_version($api_version);
			
			$segments = array_slice($segments, 3);
							
			$this->uri->segments = $segments;
		}

		// Turn the segment array into a URI string
		$uri = implode('/', $this->uri->segments);

		// Is there a literal match?  If so we're done
		if (isset($this->routes[$uri]))
		{
			$this->_set_request(explode('/', $this->routes[$uri]));		
			return;
		}
				
		// Loop through the route array looking for wild-cards
		foreach ($this->routes as $key => $val)
		{						
			// Convert wild-cards to RegEx
			$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
			
			// Does the RegEx match?
			if (preg_match('#^'.$key.'$#', $uri))
			{			
				// Do we have a back-reference?
				if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
				{
					$val = preg_replace('#^'.$key.'$#', $val, $uri);
				}
				$this->_set_request(explode('/', $val));
				return;
			}
		}

		// If we got this far it means we didn't encounter a
		// matching route so we'll set the site default route
		$this->_set_request($this->uri->segments);
	}
}