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

class DetailsException extends Exception {}

/**
 * Class Details
 * @property VBX_Call $vbx_call
 */
class Details extends User_Controller
{
	const PAGE_SIZE = 20;
	
	function __construct()
	{
		parent::__construct();
		$this->section = 'messages';
		$this->template->write('title', 'Message');
	}

	function index($message_id, $action = 'read')
	{
		return $this->details($message_id, $action);
	}
	
	function details($message_id, $action = 'read')
	{
		switch($action)
		{
			case 'annotations':
				return $this->annotations_handler($message_id);
			case 'callback':
				return $this->callback_handler($message_id);
			default:
				return $this->details_handler($message_id);
		}
	}

	private function annotations_handler($message_id)
	{
		switch($this->request_method)
		{
			case 'POST':
				return $this->add_annotation($message_id);
			case 'GET':
			default:
				return $this->get_annotations($message_id);
		}
	}
	
	private function add_annotation($message_id)
	{
		$message = $this->vbx_message->get_message($message_id);
		if(!$message)
		{
			return redirect('messages/inbox');
		}
		
		$annotation_type = $this->input->post('annotation_type');
		$description = $this->input->post('description');

		if(empty($description) || empty($annotation_type))
		{
			$error_message = 'Empty ';
			$error_message .= empty($description)? '[description] ' : '';
			$error_message .= empty($annotation_type)? '[annotation type] ' : '';
			
			$this->session->set_flashdata('error', $error_message);
			$data['json'] = array(
				'error' => true,
				'message' => $error_message
			);
		}
		else
		{
			$annotation_id = $this->vbx_message->annotate($message_id,
														  $this->user_id,
														  $description,
														  $annotation_type);
			
			$annotation = null;
			if(!empty($annotation_id))
			{
				$annotation = $this->vbx_message->get_annotation($annotation_id);
			}
			
			$data['json'] = array(
				'message' => '',
				'annotation' => $annotation
			);
		}
		
		if($this->response_type != 'json')
		{
			redirect('messages/details/'.$message_id);
		}
		
		$this->respond('', 'message_annotations', $data);	
	}

	private function get_annotations($message_id)
	{
		$offset = intval($this->input->get('offset'));
		$max = intval($this->input->get('max'));
		if(!$offset)
		{
			$offset = 0;
		}

		if(!$max)
		{
			$max = self::PAGE_SIZE;
		}
		
		$message = $this->vbx_message->get_message($message_id);
		if(!$message)
		{
			return redirect('messages/inbox');
		}
		
		$data = $this->init_view_data();
		$annotations = $this->vbx_message->get_annotations($message_id);
		$items = array_slice($annotations, $offset, $max);
		
		foreach($items as $item_id => $item)
		{
			$annotations[$item_id]->created = date('c', strtotime($item->created));
		}

		$data['json'] = array(
			'items' => $items,
			'offset' => $offset,
			'max' => $max,
			'total' => count($annotations)
		);
		
		$this->respond('', 'message_annotations', $data);
	}

	private function details_handler($message_id)
	{
		switch($this->request_method)
		{
			case 'POST':
				return $this->update_details($message_id);
			default:
				return $this->get_details($message_id);
		}
	}

	private function update_details($message_id)
	{
		$message_ids = explode(',', $message_id);
		
		$archived = $this->input->post('archived');
		$assigned = $this->input->post('assigned');
		$ticket_status = strtolower($this->input->post('ticket_status'));
		$this->load->model('vbx_user');

		$messages = array();
		$data['json'] = array(
			'message' => '',
			'error' => false
		);
		
		try
		{
			foreach($message_ids as $message_id)
			{
				try
				{
					$messages[] = $this->vbx_message->get_message($message_id);
				}
				catch(MessageException $e)
				{
					throw new DetailsException($e->getMessage());
				}
			}
			
			foreach($messages as $message)
			{
				if(empty($message))
				{
					throw new DetailsException("Message[$message_id] does not exist");
				}
				
				if($assigned)
				{
					$assignee = VBX_User::get($assigned);
				}
			}

			foreach($messages as $message)
			{
				try
				{
					if($assigned)
					{
						$this->vbx_message->assign($message->id,
											   $this->user_id,
											   $assignee);
					}
				
					if(!empty($archived))
					{
						$this->vbx_message->archive($message->id,
												$this->user_id,
												$archived);
					}
					
					if(!empty($ticket_status))
					{
						$this->vbx_message->ticket_status($message->id,
													  $this->user_id,
													  $ticket_status);
					}
				}
				catch(MessageException $e)
				{
					throw new DetailsException($e->getMessage());
				}
			}
		}
		catch(DetailsException $e)
		{
			$data['json'] = array(
				'error' => true,
				'message' => $e->getMessage()
			);
		}

		if($this->response_type == 'html')
		{
			redirect('messages/details/'.$message_id);
		}
		
		$this->respond('', 'messages', $data);
	}

	private function get_details($message_id) {

		$max_annotations = $this->input->get('max_annotations');

		if(empty($max_annotations))
		{
			$max_annotations = 20;
		}
		
		$data = $this->init_view_data();
		try
		{
			$message = $this->vbx_message->get_message($message_id);
			if(!$message)
			{
				throw new MessageException("Unable to retrieve message: $message_id");
			}
		}
		catch (VBX_MessageException $e)
		{
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('messages/inbox');
		}
		catch (MessageException $e)
		{
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('messages/inbox');
		}
		
		$this->vbx_message->mark_read($message->id, $this->user_id);
		
		if($message->owner_type == 'user' && $message->owner_id != $this->session->userdata('user_id')
		   && !in_array($message->owner_id, array_keys($data['counts'])))
		{
			$this->session->set_flashdata('You are not allowed to view that message');
			redirect('messages');
		}

		$data['group'] = '';
		if($message->owner_type == 'group')
		{
			if(isset($data['counts'][$message->owner_id])) 
			{
				$data['group'] = $data['counts'][$message->owner_id]->name;
			} 
			else 
			{
				$data['group'] = 'Inbox';
			}
		}
		
		$message->pretty_called = format_phone($message->called);
		$message->pretty_caller = format_phone($message->caller);
		
		$data['message'] = $message;
		
		$summary = $message->content_text;
		$this->load->model('vbx_user');
		$annotations = array();
		// $users = $this->vbx_user->get_active_users();
		$users = VBX_User::search(array('is_active' => 1));
		$active_users = array();
		foreach($users as $active_user)
		{
			$active_users[] = array(
				'id' => $active_user->id,
				'first_name' => $active_user->first_name,
				'last_name' => $active_user->last_name,
				'email' => $active_user->email,
			);
		}

		$folder_id = $message->owner_type == 'group' ? $message->owner_id : 0;
		
		$details = array(
			 'id' => $message_id,
			 'selected_folder' => $this->session->flashdata('selected-folder'),
			 'selected_folder_id' => $this->session->flashdata('selected-folder-id'),
			 'status' => $message->status,
			 'type' => $message->type,
			 'ticket_status' => $message->ticket_status,
			 'summary' => $summary,
			 'assigned' => $message->assigned_to,
			 'archived' => ($message->status == 'archived')? true : false,
			 'unread' => ($message->status == 'new')? true : false,
			 'recording_url' => preg_replace('/http:\/\//', 'https://', $message->content_url),
			 'recording_length' => format_player_time($message->size),
			 'received_time' => date('Y-M-d\TH:i:s+00:00', strtotime($message->created)),
			 'last_updated' => date('Y-M-d\TH:i:s+00:00', strtotime($message->updated)),
			 'called' => format_phone($message->called),
			 'caller' => format_phone($message->caller),
			 'original_called' => $message->called,
			 'original_caller' => $message->caller,
			 'folder' => $data['group'],
			 'folder_id' => $folder_id,
			 'message_type' => $message->type,
			 'active_users' => $active_users,
			 'owner_type' => $message->owner_type,
		);
		
		$data = array_merge($data, $details);
		
		$data['json'] = $details;

		if($max_annotations)
		{
			$annotations = $this->vbx_message->get_annotations($message_id);
			$items = array_slice($annotations, 0, $max_annotations);
			foreach($items as $item_id => $item)
			{
				$items[$item_id]->created = date('c', strtotime($item->created));
			}
			
			$max_annotations = (count($annotations) > $max_annotations)?
				 $max_annotations : count($annotations);
			$annotation_details = array(
				'items' => $items,
				'max' => $max_annotations,
				'total' => count($annotations)
			);
			$data['annotations'] = $data['json']['annotations'] = $annotation_details;
		}
		
		$data['gravatars'] = $this->vbx_settings->get('gravatars', $this->tenant->id);
		$data['default_gravatar'] = asset_url('assets/i/user-icon.png');
		
		$date = date('M j, Y h:i:s', strtotime($message->created));
		$this->respond(' - '.$data['group']. " voicemail from  {$message->pretty_caller} at {$date} ", 'messages/details', $data);
	}

	function get_callbacks($message_id)
	{
		if(!($message = $this->vbx_message->get_message($message_id)))
		{
			$data['json']['message'] = 'Message not found';
			return $this->respond('', 'message_callback', $data);
		}

		$annotations = $this->vbx_message->get_message_annotations($message_id, 'callback');
		foreach($annotations as $item_id => $item)
		{
			$annotations[$item_id]->created = date('c', strtotime($item->created));
		}
		
		$data['json'] = array(
			'callbacks' => $annotations
		);
		
		$this->respond('', 'message_callback', $data);
	}

	function callback($message_id)
	{
		if(!($message = $this->vbx_message->get_message($message_id)))
		{
			$data['json']['message'] = 'Message not found';
			return $this->respond('', 'message_callback', $data);
		}
		
		return $this->call($message_id);
	}

	function call($message_id = false)
	{
		$to = $this->input->post('to');
		$callerid = $this->input->post('callerid');
		$from = $this->input->post('from');
		$log_only = $this->input->post('log_only');
		
		$rest_access = $this->make_rest_access();

		$this->load->model('vbx_call');
		
		$json['message'] = '';
		$json['error'] = false;
		try
		{
			if (empty($log_only)) {
				$this->vbx_call->make_call($from, $to, $callerid, $rest_access);
			}
			
			if($message_id)
			{
				/* TODO: Move this to after call has been completed. */
				$annotation_id = $this->vbx_message->annotate($message_id,
															  $this->user_id,
															  'Called back from voicemail',
															  'called');
			}
		}
		catch(VBX_CallException $e)
		{
			$json['error'] = true;
			$json['message'] = $e->getMessage();
		}

		$data['json'] = $json;
		
		$this->respond('', 'message_call', $data);
	}

	function callback_handler($message_id)
	{
		switch($this->request_method)
		{
			case 'POST':
				return $this->callback($message_id);
			case 'GET':
				return $this->get_callbacks($message_id);
		}
	}
}