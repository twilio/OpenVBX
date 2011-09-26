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

define('VBX_ROOT', dirname( substr(BASEPATH, 0, strlen(BASEPATH) - 1 )) . '');
define('VBX_PARENT_TENANT', 1);
define('PLUGIN_PATH', VBX_ROOT . '/plugins');

require_once BASEPATH . '../OpenVBX/libraries/PhoneNumber.php';
require_once BASEPATH . '../OpenVBX/libraries/Plugin.php';
require_once BASEPATH . '../OpenVBX/libraries/AppletUI.php';
require_once BASEPATH . '../OpenVBX/libraries/OpenVBX.php';
require_once BASEPATH . '../OpenVBX/libraries/PluginData.php';
require_once BASEPATH . '../OpenVBX/libraries/PluginStore.php'; // Deprecating in 0.75
require_once BASEPATH . '../OpenVBX/libraries/FlowStore.php';
require_once BASEPATH . '../OpenVBX/libraries/AppletInstance.php';

class MY_Controller extends Controller
{
	protected $user_id;
	protected $section;
	protected $request_method;
	protected $response_type;
	public $tenant;

	public $twilio_sid;
	public $twilio_token;
	public $twilio_endpoint;

	public $tropo_username;
	public $tropo_password;

	public $voicevault_id;
	public $voicevault_username;
	public $voicevault_password;
	public $voicevault_config;
	public $voicevault_organisation;

	public $testing_mode = false;
	public $domain;

	public function __construct()
	{
		parent::__construct();

		if(!file_exists(APPPATH . 'config/openvbx.php')
		   || !file_exists(APPPATH . 'config/database.php'))
		{
			redirect('install');
		}

		$this->config->load('openvbx');

		// check for required configuration values
		$this->load->database();
		$this->load->model('vbx_settings');
		$this->load->model('vbx_user');
		$this->load->model('vbx_group');
		$this->load->model('vbx_flow');
		$this->load->model('vbx_flow_store');
		$this->load->model('vbx_plugin_store');
		$this->load->helper('file');
		$this->load->helper('html');

		$this->settings = new VBX_Settings();

		$rewrite_enabled = intval($this->settings->get('rewrite_enabled', VBX_PARENT_TENANT));
		if($rewrite_enabled)
		{
			/* For mod_rewrite */
			$this->config->set_item('index_page', '');
		}

		$this->tenant = $this->settings->get_tenant($this->router->tenant);
		if($this->tenant === false)
		{
			$this->router->tenant = '';
			redirect('');
		}

		$this->testing_mode = !empty($_REQUEST['vbx_testing_key'])? $_REQUEST['vbx_testing_key'] == $this->config->item('testing-key') : false;
		if($this->tenant)
		{
			$this->config->set_item('sess_cookie_name', $this->tenant->id . '-' . $this->config->item('sess_cookie_name'));
			$this->load->library('session');
			$this->twilio_sid = $this->settings->get('twilio_sid', $this->tenant->id);
			$this->twilio_token = $this->settings->get('twilio_token', $this->tenant->id);
			$this->twilio_endpoint = $this->settings->get('twilio_endpoint', VBX_PARENT_TENANT);
		}

		/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
		$this->tropo_username = $this->settings->get('tropo_username', $this->tenant->id);
		$this->tropo_password = $this->settings->get('tropo_password', $this->tenant->id);
		$this->voicevault_id = $this->settings->get('voicevault_id', $this->tenant->id);
		$this->voicevault_username = $this->settings->get('voicevault_username', $this->tenant->id);
		$this->voicevault_password = $this->settings->get('voicevault_password', $this->tenant->id);
		$this->voicevault_config = $this->settings->get('voicevault_config', $this->tenant->id);
		$this->voicevault_organisation = $this->settings->get('voicevault_organisation', $this->tenant->id);
		/** End Disruptive Technologies code **/

		$this->output->enable_profiler($this->config->item('enable_profiler', false));

		$this->set_response_type();
		$this->set_request_method();

		$scripts = null;
		if ($this->config->item('use_unminimized_js'))
		{
			$sources_file = APPPATH . 'assets/j/site-bootstrap.sources';
			$scripts = explode("\n", file_get_contents(APPPATH . '../assets/j/site-bootstrap.sources'));
		}
		else {
			$scripts = array('site.js');
		}

		if ($this->config->item('use_unminimized_css'))
		{
			$sources_file = APPPATH . 'assets/c/site-css.sources';
			$styles = explode("\n", file_get_contents(APPPATH . '../assets/c/site-css.sources'));
		} else {
			$styles = array('site-' . $this->config->item('site_rev') . '.css');
		}

		foreach ($scripts as $script)
		{
			if ($script) $this->template->add_js("assets/j/$script");
		}

		foreach ($styles as $style)
		{
			if ($style) $this->template->add_css("assets/c/$style");
		}
	}



	protected function set_request_method($method = null)
	{
		$this->request_method = $_SERVER['REQUEST_METHOD'];
		if($method)
		{
			$this->request_method = $method;
		}
	}

	protected function set_response_type($type = null)
	{
		$version = $this->settings->get('version', 1);

		header("X-OpenVBX-Version: $version");
		if(isset($_SERVER['HTTP_ACCEPT']))
		{
			$accepts = explode(',', $_SERVER['HTTP_ACCEPT']);
			if(in_array('application/json', $accepts)
			   && strtolower($this->router->class) != 'page') {
				header('Content-Type: application/json');
				$this->response_type = 'json';
			}
		}

		if($type)
		{
			$this->response_type = $type;
		} else if(!$this->response_type) {
			$this->response_type = 'html';
		}
	}

	protected function build_json_response($json)
	{
		/* Nothing to do here */
		return $json;
	}

	protected function json_respond($json)
	{
		$pprint = $this->input->get_post('pprint');
		/* Filter out standard templates vars */
		$json = $this->build_json_response($json);
		$json_str = json_encode($json);
		if(!$pprint)
		{
			echo $json_str;
			return;
		}

		echo json_pprint($json_str);
		return;
	}

	protected function get_navigation($logged_in, $is_admin)
	{
		$nav = array();
		$nav['util_links'] = array();
		try
		{
			$plugins = Plugin::all();
		}
		catch(PluginException $e)
		{
			$plugins = array();
			/* TODO: Properly notify user of malfunction of plugin */
		}

		$plugin_links = array();
		foreach($plugins as $plugin)
		{
			try
			{
				$plugin_links = array_merge_recursive($plugin_links, $plugin->getLinks());
			}
			catch(PluginException $e)
			{
				error_log($e->getMessage());
				$ci = &get_instance();
				$ci->session->set_flashdata('error', 'Failed to fetch link information: '.$e->getMessage());
			}
		}

		if(!empty($plugin_links['plugin_menus']))
		{
			$nav['plugin_menus'] = $plugin_links['plugin_menus'];
		}

		if($logged_in)
		{
			$nav['util_links'] = array(
									   'account' => 'My Account',
									   'auth/logout' => 'Log Out',
									   );

			if(!empty($plugin_links['util_links']))
			{
				$nav['util_links'] = array_merge($nav['util_links'],
												 $plugin_links['util_links']);

			}

			$nav['setup_links'] = array();

		    $nav['setup_links'] = array(
								   'devices' => 'Devices',
								   'voicemail' => 'Voicemail',
								   );

			if(!empty($plugin_links['setup_links']))
			{
				$nav['setup_links'] = array_merge($nav['setup_links'],
												  $plugin_links['setup_links']);

			}


			$nav['log_links'] = array();

			$nav['admin_links'] = array();

			$nav['site_admin_links'] = array();

			if($is_admin)
			{

				$nav['log_links'] = array(
										  );

				if(!empty($plugin_links['log_links']))
				{
					$nav['log_links'] = array_merge($nav['log_links'],
												$plugin_links['log_links']);

				}

				$nav['admin_links'] = array(
										   'flows' => 'Flows',
										   'numbers' => 'Numbers',
										   'accounts' => 'Users',
										   'settings/site' => 'Settings',
										   );

				/* Support plugins that used site_admin */
				if(!empty($plugin_links['site_admin_links']))
				{
					$nav['admin_links'] = array_merge($nav['admin_links'],
													  $plugin_links['site_admin_links']);
				}

				/* Include plugins that refer to 'admin' menu */
				if(!empty($plugin_links['admin_links']))
				{
					$nav['admin_links'] = array_merge($nav['admin_links'],
													  $plugin_links['admin_links']);
				}
			}
		}

		return $nav;
	}

	protected function template_respond($title, $section, $payload, $layout, $layout_dir = 'content')
	{
		$this->template->write('title', $title);

		if(isset($payload['json']))
		{
			unset($payload['json']);
		}

		$theme = 'default';
		if($this->tenant)
		{
			$theme = $this->settings->get('theme', $this->tenant->id);
			if(empty($theme))
			{
				$theme = 'default';
			}
		}

		$css = array("themes/$theme/style");

		$theme_config = @parse_ini_file('assets/themes/'.$theme.'/config.ini');
		if(!$theme_config)
		{
			$theme_config = array();
			$theme_config['site_title'] = 'VBX';
		}

		$payload['session_id'] = $this->session->userdata('session_id');
		$payload['theme'] = $theme;
		$payload['site_title'] = isset($theme_config['site_title'])? $theme_config['site_title'] : '';
		$payload['css']	 = $css;
		$payload['is_admin'] = $this->session->userdata('is_admin');
		$payload['email'] = $this->session->userdata('email');
		$payload['logged_in'] = $this->session->userdata('loggedin');
		$payload['site_rev'] = $this->config->item('site_rev');
		$payload['asset_root'] = ASSET_ROOT;
		$payload['layout'] = $layout;

		if($layout == 'yui-t2')
		{
			$payload['layout_override'] = 'yui-override-main-margin';
		}
		else
		{
			$payload['layout_override'] = '';
		}

		if($user = VBX_User::get($this->session->userdata('user_id'))) {
			if ($user->online == 9) {
				$payload['user_online'] = 'client-first-run';
			}
			else {
				$payload['user_online'] = (bool) $user->online;
			}
		}

		$navigation = $this->get_navigation($this->session->userdata('loggedin'),
											$this->session->userdata('is_admin'));
		$payload = array_merge($payload, $navigation);
		$payload = $this->template->clean_output($payload);

		$this->template->write_view('wrapper_header', $layout_dir.'/wrapper_header', $payload);
		$this->template->write_view('header', $layout_dir.'/header', $payload);
		$this->template->write_view('utility_menu', $layout_dir.'/utility_menu', $payload);
		$this->template->write_view('context_menu', $layout_dir.'/context_menu', $payload);
		$this->template->write_view('content_header', $layout_dir.'/content_header', $payload);
		$this->template->write_view('content_sidebar', $layout_dir.'/content_sidebar', $payload);
		$this->template->write_view('content_footer', $layout_dir.'/content_footer', $payload);
		$this->template->write_view('footer', $layout_dir.'/footer', $payload);
		$this->template->write_view('wrapper_footer', $layout_dir.'/wrapper_footer', $payload);
		$this->template->write_view('error_dialog', $layout_dir.'/error_dialog', $payload);
		$this->template->write_view('analytics', $layout_dir.'/analytics', $payload);
		$this->template->write_view('content', $section, $payload);
		$content = $this->template->render('content');
		$this->template->write_view('content_main', $layout_dir.'/content_main', compact('content'));

		if($this->input->get_post('no_layout') == 1)
		{
			echo $this->template->render('content_main');
			return;
		}

		if($this->input->get_post('barebones') == 1)
		{
			echo $this->template->render('content');
			return;
		}

		return $this->template->render();
	}

	protected function json_respond_not_implemented($message) {
		echo json_encode(compact('message'));
	}

	protected function respond($title, $section, $payload, $layout = 'yui-t2', $layout_dir = 'layout/content')
	{
		if(!headers_sent())
		{
			$this->session->persist();
		}
		else
		{
			error_log('Unable to write session, headers already sent');
		}

		switch($this->response_type)
		{
			case 'json':
				if(isset($payload['json'])) {
					$json = $payload['json'];
					$this->json_respond($json);
				} else {
					$message = 'Not Implemented';
					$this->json_respond_not_implemented($message);
				}
				break;
			default:
				$this->template_respond($title, $section, $payload, $layout, $layout_dir);
				break;
		}
	}

	public function getTenant()
	{
		return $this->tenant;
	}
}

require_once 'User_Controller.php';
