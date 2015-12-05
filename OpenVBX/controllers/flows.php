<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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

require_once(APPPATH.'libraries/twilio.php');

/**
 * Class Flows
 * @property MY_Pagination $pagination
 */
class Flows extends User_Controller {

	private $flows_per_page = '50';

	function __construct()
	{
		parent::__construct();
		$this->load->library('applet');
		$this->section = 'flows';
		$this->admin_only($this->section);
		$this->load->library('pagination');
	}

	function index()
	{
		switch($this->request_method)
		{
			case 'POST':
				return $this->create();
				break;
			case 'GET':
			default:
				return $this->flows();
		}
	}
	
	private function flows()
	{
		$max = $this->input->get_post('max');
		$offset = $this->input->get_post('offset');
		
		if (empty($max)) {
			$max = $this->flows_per_page;
		}
		
		$this->template->add_js('assets/j/flows.js');
		
		$data = $this->init_view_data();
		
		$flows = VBX_Flow::search(array(), $max, $offset);
		if(empty($flows))
		{
			set_banner('flows', 'Flows', $this->load->view('banners/flows-start', array(), true));
		}

		$flows_with_numbers = array();
		foreach($flows as $flow)
		{
			$flows_with_numbers[] = array(
				'id' => $flow->id,
				'name' => trim($flow->name),
				'numbers' => $flow->numbers,
				'voice_data' => $flow->data,
				'sms_data' => $flow->sms_data,
			);
		}
		
		$data['items'] = $flows_with_numbers;
		$data['highlighted_flows'] = array($this->session->flashdata('flow-first-save', 0));
		
		// pagination
		$total_items = VBX_Flow::count();
		$page_config = array(
			'base_url' => site_url('flows/'),
			'total_rows' => $total_items,
			'per_page' => $max
		);
		$this->pagination->initialize($page_config);
		$data['pagination'] = CI_Template::literal($this->pagination->create_links());
		
		$this->respond('Call Flows', 'flows', $data);
	}

	function edit($flow_id)
	{
		switch($this->request_method)
		{
			case 'POST':
				return $this->save($flow_id);
			case 'DELETE':
				return $this->delete($flow_id);
			case 'GET':
			default:
				return $this->flow_editor($flow_id);
		}
	}

	function sms($flow_id)
	{
		switch($this->request_method)
		{
			case 'POST':
				return $this->save($flow_id);
			case 'DELETE':
				return $this->delete($flow_id);
			case 'GET':
			default:
				return $this->flow_editor($flow_id, 'sms');
		}
	}

	function copy($flow_id)
	{
		$old = VBX_Flow::get($flow_id);
		$data['json'] = array('error' => false, 'message' => '');

		if(!empty($old))
		{
			$f = new VBX_Flow();
			$f->user_id = $this->user_id;
			$f->name = trim($this->input->post('name'));
			$f->data = $old->data;
			$f->sms_data = $old->sms_data;

			try
			{
				$f->save();
				$data['json']['id'] = $f->id;
				$data['json']['name'] = $f->name;
			}
			catch(VBX_FlowException $e)
			{
				$data['json']['error']	= true;
				$data['json']['message'] = $e->getMessage();
			}
		}
		else
		{
			$data['json']['error']	= true;
			$data['json']['message'] = "Flow $flow_id does not exist.";
		}

		$this->respond('', null, $data);
	}

	private function create()
	{
		$flow = new VBX_Flow();
		$flow->user_id = $this->user_id;
		$flow->name = trim($this->input->post('name'));
		$data['json'] = array('error' => false, 'message' => '');
		try
		{
			$flow->save();
			if($this->response_type == 'html')
			{
				return redirect('flows/edit/' . $flow->id);
			}
			$data['json']['id'] = $flow->id;
			$data['json']['name'] = $flow->name;
			$data['json']['url'] = site_url('flows/edit/'.$flow->id);
		}
		catch(VBX_FlowException $e)
		{
			$data['json']['message'] = $e->getMessage();
			$data['json']['error'] = true;
			
			$this->session->set_flashdata('error', $data['json']['message']);
			if($this->response_type == 'html')
			{
				return redirect('flows' . $flow->id);
			}
		}

		$this->respond('', 'flows', $data);
	}


	private function delete($id)
	{
		$f = VBX_Flow::get($id);
		
		$data['json'] = array('error' => false, 'message' => '');
		if(!empty($f))
		{
			$f->delete();
		}
		else
		{
			$message = "Flow $id does not exist.";
			$data['json']['message'] = $message;
			$this->session->set_flashdata('error', $message);
		}

		if($this->response_type == 'json')
		{
			return $this->respond('', 'flow', $data, 'yui-t7');
		}
		
		return redirect('flows');
	}


	private function flow_editor($id = 0, $type = 'voice')
	{
		if($id < 1)
		{
			redirect('flows');
		}

		$applets = Applet::get_applets($type);
		$data = $this->init_view_data(false);

		$this->template->add_js('assets/j/flow.js');
		$this->template->add_js('assets/j/accounts.js');
		$this->template->add_js('assets/j/plugins/jquery.address-1.0.min.js');

		foreach($applets as $applet)
		{
			if ($this->config->item('use_unminimized_js') && !empty($applet->script_url))
			{
				$this->template->add_js($applet->script_url, 'absolute');
			}
			if ($this->config->item('use_unminimized_css')
				&& !empty($applet->style_url))
			{
				$this->template->add_css($applet->style_url, 'link');
			}
		}

		if (!$this->config->item('use_unminimized_js')) {
			$this->template->add_js(site_url('/flows/scripts'), 'absolute');
		}
		if (!$this->config->item('use_unminimized_css')) {
			$this->template->add_css(site_url('flows/styles'), 'link');
		}
		
		$flow = VBX_Flow::get($id);
		
		if(empty($flow))
		{
			$this->session->set_flashdata('error', "Flow $id does not exist.");
			return redirect('flows');
		}

		$flow_data = array();
		$flow_obj = null;
		switch($type)
		{
			case 'sms':
				if(!is_null($flow->sms_data))
				{
					$flow_obj = json_decode($flow->sms_data);
				}
				break;
			case 'voice':
				if(!is_null($flow->data))
				{
					$flow_obj = json_decode($flow->data);
				}
				break;
		}
		if(!is_null($flow_obj))
		{
			$flow_data = get_object_vars($flow_obj);
		}

		// add start instance if it's not there
		if(!isset($flow_data['start']))
		{
			$temp_start = new stdClass();
			$temp_start->name = 'Flow Start';
			$temp_start->id = 'start';
			$temp_start->type = 'standard---start';
			$temp_start->data = FALSE;
			$temp_start->sms_data = FALSE;
			$flow_data['start'] = $temp_start;
		}

		Applet::$flow_data =& $flow_data;	// make flow data visible to all applets

		$data['flow_data'] = $flow_data;
		$data['applets']  = $applets;
		$data['editor_type'] = $type;
		$data['flow'] = $flow;
		$flow_label = ($type == 'voice')? 'Voice' : 'SMS';
		$this->template->write('title', "Edit $flow_label Flow");
		$this->respond('Flow Editor', 'flow', $data, 'flow-editor-wrapper', 'layout/flow-editor' );
	}
	
	private function save($flow_id)
	{
		$error = false;
		$message = '';

		$flow = new VBX_Flow();
		if($flow_id > 0)
		{
			$flow = VBX_Flow::get($flow_id);
			if(empty($flow))
			{
				$error = true;
				$message = 'Flow does not exist.';
			}
		}

		$flow->name = trim($this->input->post('name'));
	
		$voice_data = $this->input->post('data');
		$sms_data = $this->input->post('sms_data');

		if(!empty($voice_data))
		{
			$flow->data = $voice_data;
		}
		
		if(!empty($sms_data))
		{
			$flow->sms_data = $sms_data;
		}

		try
		{
			$flow->save();
			$this->session->set_flashdata('flow-first-save', $flow->id);
		}
		catch(VBX_FlowException $e)
		{
			$error = true;
			$message = 'Failed to save flow.';
		}
		
		$flow_url = site_url('flows/edit/'.$flow->id);

		if($this->response_type != 'json')
		{
			return redirect($flow_url);
		}
		
		$data['json'] = array('error' => $error,
							  'message' => $message,
							  'flow_id' => $flow->id,
							  'flow_url' => $flow_url);
		
		$this->respond('Call Flows', 'flows', $data);
	}

	// combine all applet scripts together and output
	function scripts()
	{
		$applets = Applet::get_applets();
		$names = array();

		foreach($applets as $applet) {
			$script = file_exists($applet->script_file)? file_get_contents($applet->script_file) : '';
			if(!empty($script))
			{
				$all_scripts[] = $script;
			}
			
			$names[$applet->id] = $applet->name;
		}

		$all_scripts[] = 'var applet_names = ' . json_encode($names) . ';';
		
		header('Content-type: text/javascript');
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600) . " GMT");

		echo join("\n", $all_scripts);
		exit(0);
	}

	// combine all applet stylesheets together and output
	function styles()
	{
		$applets = Applet::get_applets();
		$all_styles = array();
		
		foreach($applets as $applet) {
			$style = file_exists($applet->style_file)? file_get_contents($applet->style_file) : '';
			if(!empty($style))
			{
				$all_styles[] = $style;
			}
		}

		header('Content-type: text/css');
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600) . " GMT");

		echo join("\n", $all_styles);
		exit(0);
	}
}
