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

class NumbersException extends Exception {}

/**
 * Class Numbers
 * @property MY_Pagination $pagination
 */
class Numbers extends User_Controller
{
	protected $flows;

	private $error_message = FALSE;

	/**
	 * @var stdClass
	 */
	private $new_number;

	private $numbers_per_page = 50;

	function __construct()
	{
		parent::__construct();
		$this->section = 'numbers';
		$this->template->write('title', 'Numbers');
		$this->load->model('vbx_incoming_numbers');
		$this->load->library('pagination');
	}

	function index()
	{
		$this->admin_only($this->section);
		$this->template->add_js('assets/j/numbers.js');

		$max = $this->input->get_post('max');
		$offset = $this->input->get_post('offset');

		if (empty($offset)) {
			$offset = 0;
		}

		if (empty($max)) {
			$max = $this->numbers_per_page;
		}

		$data = $this->init_view_data();
		$data['selected_country'] = $this->vbx_settings->get('numbers_country', $this->tenant->id);
		
		$numbers = array();
		$total_numbers = 0;
		$data['countries'] = array();
		$data['openvbx_js']['countries'] = array();
		try
		{
			$numbers = $this->vbx_incoming_numbers->get_numbers();
			$countries = $this->vbx_incoming_numbers->get_available_countries();

			// lighten the payload as we don't need the url data in the view
			foreach ($countries as &$country)
			{
				$data['countries'][$country->country_code] = $country->country;
				$country->available = array_keys(get_object_vars($country->subresource_uris));
				unset($country->uri, $country->subresource_uris);
			}

			$data['openvbx_js']['countries'] = json_encode($countries);
		}
		catch (VBX_IncomingNumberException $e)
		{
			$this->error_message = $e->getMessage();
		}

		$data['incoming_numbers'] = array();
		$data['available_numbers'] = array();
		$data['other_numbers'] = array();
		$data['count_real_numbers'] = 0;
		
		// now generate table
		if(count($numbers) > 0)
		{
			$this->flows = $this->get_flows_list();
			$data['flow_options'] = $this->get_flow_options($this->flows);
			
			foreach($numbers as $item)
			{
				$item_msg = '';
				if(is_object($this->new_number) && $this->new_number->id == $item->id)
				{
					$item_msg = 'New';
				}
				
				$item->phone_formatted = format_phone($item->phone);

				$capabilities = array();
				if (!empty($item->capabilities)) 
				{
					foreach ($item->capabilities as $cap => $enabled)
					{
						if ($enabled) 
						{
							array_push($capabilities, ucfirst($cap));
						}
					}
				}
				$item->capabilities = $capabilities;

				$item->status = null;

				if ($item->installed)
				{
					// Number is installed in this instance of OpenVBX
					$item->trial = (isset($item->trial) && $item->trial == 1 ? 1 : 0);
					$item->status = $item_msg;
					
					array_push($data['incoming_numbers'], $item);
				}
				elseif ((!empty($item->url) || !empty($item->smsUrl)) && $offset == 0)
				{
					// Number is in use elsewhere
					array_push($data['other_numbers'], $item);
				}
				elseif ($offset == 0)
				{
					// Number is open for use
					array_push($data['available_numbers'], $item);
				}
				
				if ($item->id !== 'Sandbox') 
				{
					$data['count_real_numbers']++;
				}
			}
		}
		$data['highlighted_numbers'] = array($this->session->flashdata('new-number'));
		$data['twilio_sid'] = $this->twilio_sid;

		if(empty($this->error_message))
		{
			$error_message = $this->session->flashdata('error');
			if(!empty($error_message))
			{
				$this->error_message = $this->session->flashdata('error');
			}
		}

		if(!empty($this->error_message))
		{
			$data['error'] = CI_Template::literal($this->error_message);
		}

		$data['counts'] = $this->message_counts();

		/**
		 * $numbers is a list of phone numbers straight from the Twilio API,
		 * there's no fancy query logic here, just slicing up the array.
		 */
		$total_numbers = count($data['incoming_numbers']);
		$data['incoming_numbers'] = array_slice($data['incoming_numbers'], $offset, $max, true);

		// pagination
		$page_config = array(
			'base_url' => site_url('numbers'),
			'total_rows' => $total_numbers,
			'per_page' => $max
		);
		$this->pagination->initialize($page_config);
		$data['pagination'] = CI_Template::literal($this->pagination->create_links());

		$this->respond('', 'numbers/numbers', $data);
	}

	/**
	 * Key an ID keyed list of flows
	 *
	 * @return array
	 */
	protected function get_flows_list()
	{
		$flows = array();
		$_flows = VBX_Flow::search();

		if (count($_flows))
		{
			foreach ($_flows as $flow)
			{
				$flows[$flow->id] = $flow;
			}
		}
		
		unset($_flows);
		return $flows;
	}
	
	/**
	 * Build a list of flow options for attaching numbers to flows
	 *
	 * @param VBX_Flow[]
	 * @return array
	 */
	protected function get_flow_options($flows)
	{
		$flow_options = array();
		$flow_options['-'] = 'Connect a Flow';
		
		if (!empty($flows))
		{
			foreach ($flows as $flow)
			{
				$flow_options[$flow->id] = $flow->name;
			}
		}
		
		$flow_options['---'] = '---';
		$flow_options['new'] = 'Create a new Flow';
		
		return $flow_options;
	}

	function add()
	{
		$this->admin_only($this->section);
		$json = array( 'error' => false, 'message' => 'Added Number' );

		try
		{
			$is_local = ($this->input->post('type') == 'local');
			$area_code = $this->input->post('area_code');
			$country = $this->input->post('country');
			$this->new_number = $this->vbx_incoming_numbers->add_number($is_local, $area_code, $country);

			$json['number'] = $this->new_number;

			$this->session->set_flashdata('new-number', $this->new_number->id);
		}
		catch (VBX_IncomingNumberException $e)
		{
			$json['message'] = $e->getMessage();
			$json['error'] = true;
		}

		$data = compact('json');
		$this->respond('', 'numbers', $data);
	}

	/**
	 * @param int $phone_id
	 */
	function delete($phone_id)
	{
		$this->admin_only($this->section);
		$confirmed = $this->input->post('confirmed');
		$data['confirmed'] = $confirmed;
		$data['error'] = false;
		$data['message'] = '';
		try
		{
			if(!$confirmed)
			{
				throw new NumbersException('Incoming number is not confirmed');
			}

			if(empty($phone_id))
			{
				throw new NumbersException('Malformed Phone identifier.');
			}

			try
			{
				$this->vbx_incoming_numbers->delete_number($phone_id);
			}
			catch(VBX_IncomingNumberException $e)
			{
				throw new NumbersException($e->getMessage());
			}
		}
		catch(NumbersException $e)
		{
			$data['error'] = true;
			$data['message'] = $e->getMessage();
		}

		echo json_encode($data);
	}

	/**
	 * @param int $phone_id
	 * @param int $id id of flow to assign number to
	 */
	function change($phone_id, $id)
	{
		$this->admin_only($this->section);
		try
		{
			$success = $this->vbx_incoming_numbers->assign_flow($phone_id, $id);
			$message = '';
		}
		catch (Exception $ex)
		{
			$success = FALSE;
			$message = $ex->getMessage();
		}

		echo json_encode(compact('message', 'success', 'id'));
	}

	function token()
	{
		if($this->response_type == 'html')
		{
			redirect('numbers');
			exit;
		}

		return $this->token_handler();
	}

	function outgoingcallerid()
	{
		if($this->response_type == 'html')
		{
			redirect('numbers');
			exit;
		}

		return $this->outgoingcallerid_handler();
	}

	private function token_handler()
	{
		$data = array();
		switch($this->request_method)
		{
			case 'GET':
				$token = $this->make_rest_access();
				$data['json'] = compact('token');
				break;
			case 'POST':
			case 'DELETE':
			default:
				break;

		}


		$this->respond('', 'numbers', $data);
	}

	private function outgoingcallerid_handler()
	{
		$data = array();
		switch($this->request_method)
		{
			case 'GET':
				try
				{
					$data['json'] = $this->get_outgoingcallerid();
				}
				catch (NumbersException $e)
				{
					$data['json'] = array('error' => true,
										  'message' => $e->getMessage());
				}
				break;
			case 'POST':
			case 'DELETE':
			default:
				break;
		}

		$this->respond('', 'numbers', $data);
	}

	private function get_outgoingcallerid()
	{
		$numbers = array();
		try
		{
			$numbers = $this->vbx_incoming_numbers->get_numbers();
		}
		catch (VBX_IncomingNumberException $e)
		{
			throw new NumbersException($e->getMessage(), $e->getCode());
		}

		return $numbers;
	}

	private function make_token()
	{
		return $this->make_rest_access();
	}
	
	public function refresh_select() {
		$data = $this->init_view_data();
		$html = $this->load->view('dialer/numbers', $data, true);
		
		$response['json'] = array(
			'error' => false,
			'html' => $html
		);
		
		$this->respond('', 'dialer/numbers', $response);
	}
}