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

require_once APPPATH . 'libraries/PhoneNumber.php';
require_once APPPATH . 'libraries/Plugin.php';
require_once APPPATH . 'libraries/AppletUI.php';
require_once APPPATH . 'libraries/OpenVBX.php';
require_once APPPATH . 'libraries/PluginData.php';
require_once APPPATH . 'libraries/FlowStore.php';
require_once APPPATH . 'libraries/AppletInstance.php';
require_once APPPATH . 'libraries/Caches/Abstract.php';

/**
 * @property MY_Router $router
 * @property CI_Loader $load
 * @property MY_Config $config
 * @property MY_Session $session
 * @property CI_Template $template
 * @property CI_Input $input
 * @property CI_Output $output
 * @property VBX_Settings $vbx_settings
 * @property CI_URI $uri
 */
class MY_Controller extends Controller
{
	public $tenant;
	protected $user_id;
	protected $section;
	protected $request_method;
	protected $response_type;

	protected $assets;
	protected $js_assets = 'js';
	protected $css_assets = 'css';

	public $twilio_sid;
	public $twilio_token;
	public $twilio_endpoint;

	public $testing_mode = false;
	
	protected $suppress_warnings_notices = false;

	public function __construct()
	{
		parent::__construct();

		if(!file_exists(APPPATH . 'config/openvbx.php') 
			|| !file_exists(APPPATH . 'config/database.php'))
		{
			redirect('install');
		}

		$this->config->load('openvbx');
		$this->load->database();
		
		$this->cache = OpenVBX_Cache_Abstract::load();
		$this->api_cache = OpenVBX_Cache_Abstract::load('db');
		
		$this->load->model('vbx_settings');
		$this->load->model('vbx_user');
		$this->load->model('vbx_group');
		$this->load->model('vbx_flow');
		$this->load->model('vbx_flow_store');
		$this->load->model('vbx_plugin_store');
		$this->load->helper('file');
		$this->load->helper('twilio');
		$this->load->library('session');
		
		$this->settings = new VBX_Settings();

		$rewrite_enabled = intval($this->settings->get('rewrite_enabled', VBX_PARENT_TENANT));
		if($rewrite_enabled)
		{
			/* For mod_rewrite */
			$this->config->set_item('index_page', '');
		}

		$this->tenant = $this->settings->get_tenant($this->router->tenant);
		if(!$this->tenant || !$this->tenant->active)
		{
			$this->session->set_userdata('loggedin', 0);
			$this->session->set_flashdata('error', 'This tenant is no longer active');
			redirect(asset_url('auth/logout'));
		}
		
		if ($this->tenant && $this->tenant->url_prefix 
			&& $this->tenant->url_prefix !== $this->router->tenant)
		{
			// case sensitive url faux-pas, redirect to force the url case
			$this->router->tenant = $this->tenant->url_prefix;
			redirect(current_url());
		}

		if($this->tenant === false)
		{
			$this->router->tenant = '';
			redirect('');
		}

		$this->set_time_zone();

		$this->testing_mode = !empty($_REQUEST['vbx_testing_key'])? $_REQUEST['vbx_testing_key'] == $this->config->item('testing-key') : false;
		if($this->tenant)
		{
			$this->config->set_item('sess_cookie_name', $this->tenant->id.'-'. 
										$this->config->item('sess_cookie_name'));
			
			$this->twilio_sid = $this->settings->get('twilio_sid', $this->tenant->id);
			$token_from = ($this->tenant->type == VBX_Settings::AUTH_TYPE_CONNECT ? VBX_PARENT_TENANT : $this->tenant->id);
			$this->twilio_token = $this->settings->get('twilio_token', $token_from);				
			$this->application_sid = $this->settings->get('application_sid', $this->tenant->id);

			// @deprecated, will be removed in a future release
			$this->twilio_endpoint = $this->settings->get('twilio_endpoint', VBX_PARENT_TENANT);
		}

		$this->output->enable_profiler($this->config->item('enable_profiler', false));

		$this->set_response_type();
		$this->set_request_method();

		if ($this->response_type == 'html') 
		{
			$scripts = null;
			$js_assets = (!empty($this->js_assets) ? $this->js_assets : 'js');
			if ($this->config->item('use_unminimized_js'))
			{
				$scripts = $this->get_assets_list($js_assets);
				if (is_array($scripts)) {
					foreach ($scripts as $script)
					{
						if ($script) $this->template->add_js($script);
					}
				}
			}
			else {
				$this->template->add_js(asset_url('assets/min/?g='.$js_assets), 'absolute');
			}

			$css_assets = (!empty($this->css_assets) ? $this->css_assets : 'css');
			if ($this->config->item('use_unminimized_css'))
			{				
				$styles = $this->get_assets_list($css_assets);
				if (is_array($styles)) {
					foreach ($styles as $style)
					{
						if ($style) $this->template->add_css($style);
					}
				}
			} else {
				$this->template->add_css(asset_url('assets/min/?g='.$css_assets), 'link');
			}
		}
		
		/**
		 * Controllers can elect to suppress the error reporting - this is mainly to
		 * keep API & Ajax responses from failing due to Warnings & Notices. Use carefully.
		 */
		if ($this->suppress_warnings_notices) {
			ini_set('display_errors', 'off');
		}
	}
	
	/**
	 * Called when no minimizing assets
	 * Import the minification group definitions & cleanse for direct inclusion
	 *
	 * @param string $type 
	 * @return mixed array | false
	 */
	protected function get_assets_list($type) {
		$_assets = array();
		if (empty($this->assets)) {
			$min_config = BASEPATH.'../assets/min/groupsConfig.php';
			if (is_file($min_config)) {
				include($min_config);
				$this->assets = $sources;
			}
		}
		if (isset($this->assets[$type])) {
			$_assets = $this->assets[$type];
			foreach ($_assets as &$asset) {
				$asset = preg_replace('|^(\.\.)|', 'assets', $asset);
			}
			return $_assets;
		}
		return false;
	}
	
	protected function set_time_zone()
	{
		$tz = $this->vbx_settings->get('server_time_zone', $this->tenant->id);
		if (!empty($tz))
		{
			date_default_timezone_set($tz);
		} 
		else 
		{
			date_default_timezone_set(date_default_timezone_get());
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
		header('X-OpenVBX-Version: '.OpenVBX::version());
		if(isset($_SERVER['HTTP_ACCEPT']))
		{
			$accepts = explode(',', $_SERVER['HTTP_ACCEPT']);
			if(in_array('application/json', $accepts) && strtolower($this->router->class) != 'page') 
			{
				header('Content-Type: application/json');
				$this->response_type = 'json';
			}
		}

		if($type)
		{
			$this->response_type = $type;
		} 
		else if(!$this->response_type) 
		{
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
		header('content-type: text/javascript');
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
				$ci->session->set_flashdata('error', 'Failed to fetch link information: '.
											$e->getMessage());
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
				'auth/logout' => 'Log Out'
			);

			if(!empty($plugin_links['util_links']))
			{
				$nav['util_links'] = array_merge($nav['util_links'], 
												$plugin_links['util_links']);
			}

			$nav['setup_links'] = array();

		    $nav['setup_links'] = array(
				'devices' => 'Devices',
				'voicemail' => 'Voicemail'
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
					'settings/site' => 'Settings'
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
		if(isset($payload['json']))
		{
			unset($payload['json']);
		}

		$theme = $this->getTheme();

		if (empty($payload['user'])) {
			$payload['user'] = VBX_user::get(array('id' => $this->session->userdata('user_id')));
		}

		$css = array("themes/$theme/style");

		$theme_config = $this->getThemeConfig($theme);

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
			if ($user->setting('online') == 9) {
				$payload['user_online'] = 'client-first-run';
			}
			else {
				$payload['user_online'] = (bool) $user->setting('online');
			}
		}

		$navigation = $this->get_navigation($this->session->userdata('loggedin'),
											$this->session->userdata('is_admin'));
		$payload = array_merge($payload, $navigation);
		$payload = $this->template->clean_output($payload);
		
		$template_regions = $this->template->get_regions();
		foreach (array_keys($template_regions) as $region) 
		{
			if (strpos($region, '_') === 0) 
			{
				continue;
			}
			
			if ($region == 'title') 
			{
				$this->template->write('title', $title);
				continue;
			}

			$view = $layout_dir . '/' . $region;
			$content = $payload;
			
			if ($region == 'content_main') 
			{
				$content = array(
					'content' => $this->template->render('content'),
				);
				$view = $layout_dir . '/content_main';
			} 
			elseif ($region == 'content') 
			{
				$view = $section;
			}
			
			$this->template->write_view($region, $view, $content);
		}

		if($this->input->get_post('no_layout') == 1)
		{
			return $this->template->render('content_main');
		}

		if($this->input->get_post('barebones') == 1)
		{
			$this->template->render('content');
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
	
	public function getTheme() {
		$theme = 'default';
		
		if($this->tenant)
		{
			$theme_setting = $this->settings->get('theme', $this->tenant->id);
			if(!empty($theme_setting))
			{
				$theme = $theme_setting;
			}
		}
		
		return $theme;
	}
	
	public function getThemeConfig($theme) {
		$theme_config = array(
			'site_title' => 'VBX'
		);
		
		$theme_config_file = 'assets/themes/'.$theme.'/config.ini';
		if (is_file($theme_config_file)) 
		{
			$imported_theme_config = @parse_ini_file($theme_config_file);
			$theme_config = array_merge($imported_theme_config, $theme_config);
		}
		
		return $theme_config;
	}
}

require_once 'User_Controller.php';
