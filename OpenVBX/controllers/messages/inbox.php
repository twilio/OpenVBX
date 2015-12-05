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

class InboxException extends Exception {}

/**
 * Class Inbox
 * @property MY_Pagination $pagination
 */
class Inbox extends User_Controller {

	const PAGE_SIZE = 20;

	function __construct()
	{
		parent::__construct();
		$this->section = 'messages';
		$this->template->write('title', 'Messages');
		$this->load->helper('date');
		$this->load->model('vbx_user');
	}

	public function scripts($group = false)
	{
		
		$max = $this->input->get_post('max');
		$offset = $this->input->get_post('offset');
		
		if(!$max)
		{
			$max = self::PAGE_SIZE;
		}
		
		$data = $this->init_view_data();
		$inbox_counts = $data['counts'];
		
		if($group && !array_key_exists($group, $inbox_counts))
		{
			redirect('messages/scripts');
			return;
		}
		
		$data['group'] = $group;
		$total_items = 0;
		$folders = array();
		if(!$group)
		{
			$data['group_name'] = 'Inbox';
			$groups = array_keys($inbox_counts);
		}
		else
		{
			$data['group_name'] = $inbox_counts[$group]->name;
			$groups = array($group);
			$folders[$group] = $inbox_counts[$group];
		}
		
		$data['active_users'] = VBX_User::search(array('is_active' => 1));
		
		$users = ($group == 0) ? array($this->user_id) : array();
		$items = array();
		$message_options = array(
			 'group' => $groups,
			 'user' => $users
		 );
		
		$messages = $this->vbx_message->get_messages($message_options,
													 $offset,
													 $max);
		
		uasort($messages['messages'], 'sort_by_date');
		foreach($messages['messages'] as $item)
		{
			$group_name = '';
			$group_id = 0;
			if($item->owner_type == 'group' && isset($inbox_counts[$item->owner_id]))
			{
				$group_name = $inbox_counts[$item->owner_id]->name;
				$group_id = $item->owner_id;
			}
			if($item->owner_type == 'user' && isset($inbox_counts[$item->owner_id]))
			{
				$group_name = 'Inbox';
				$group_id = $item->owner_id;
			}
				
			$this->session->set_flashdata('selected-folder', $group_name);
			$this->session->set_flashdata('selected-folder-id', $group_id);

			$short_summary = null;
				
			if (is_null($item->content_text))
			{
				$short_summary = "(no transcription)";
			}
			else
			{
				$short_summary = substr($item->content_text, 0, 125)
					. ((strlen($item->content_text) > 125)? '...' : '');
			}
					
			$date_recorded = date('c', strtotime($item->created));
			$date_updated = date('c', strtotime($item->updated));

			$assigned_user = null;
			foreach($data['active_users'] as $u)
			{
				if($u->id == $item->assigned_to)
				{
					$assigned_user = clone($u);
				}
			}
				
			$items[] = array(
				'id' => $item->id,
				'folder' => $group_name,
				'folder_id' => $group_id,
				'short_summary' => $short_summary,
				'assigned' => $item->assigned_to,
				'type' => $item->type,
				'assigned_user' => $assigned_user,
				'ticket_status' => $item->ticket_status,
				'archived' => ($item->status == 'archived')? true : false,
				'unread' => ($item->status == 'new')? true : false,
				'recording_url' => $item->content_url,
				'recording_length' => format_player_time($item->size),
				'received_time' => $date_recorded,
				'last_updated' => $date_updated,
				'called' => format_phone($item->called),
				'caller' => format_phone($item->caller),
				'original_called' => $item->called,
				'original_caller' => $item->caller,
				'owner_type' => $item->owner_type,
			);

		}

		$group_name = 'Inbox';

		$messageIdsToRecordingURLs = array();
		
		foreach ($items as $item) {
			$messageIdsToRecordingURLs[$item['id']] = $item['recording_url'] . ".mp3";
		}
		
		$data = array_merge($data, compact('messageIdsToRecordingURLs'));

		$messageIdsJson = json_encode($messageIdsToRecordingURLs);
		if (empty($messageIdsJson)) {
			$messageIdsJson = '{}';
		}

		header('content-type: text/javascript');
		header('Expires: Fri, 22 Mar 1974 06:30:00 GMT');
		header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		echo "$(document).ready(function(){ Message.Player.messageIdsToRecordingURLs = ".
				$messageIdsJson."; });";
	}

	function index($group = false)
	{
		return $this->inbox($group);
	}

	private function inbox($group = false)
	{
		$max = $this->input->get_post('max');
		$offset = $this->input->get_post('offset');
		$do_transcriptions = $this->vbx_settings->get('transcriptions', $this->tenant->id);

		if(!$max)
		{
			$max = self::PAGE_SIZE;
		}
		
		$this->template->add_css(asset_url('assets/c/messages.css'), 'link');
		$data = $this->init_view_data();
		$inbox_counts = $data['counts'];

		if($group && !array_key_exists($group, $inbox_counts))
		{
			redirect('messages/inbox');
			return;
		}

		$ts = time();

		$this->template->add_js('messages/scripts'.($group? '/'.$group : '').'?'.
									http_build_query(compact('max', 'offset', 'ts')), 'dynamic');
		
		$data['group'] = $group;
		$total_items = 0;
		$folders = array();
		if(!$group)
		{
			$data['group_name'] = 'Inbox';
			$groups = array_keys($inbox_counts);
		}
		else
		{
			$data['group_name'] = $inbox_counts[$group]->name;
			$groups = array($group);
			$folders[$group] = $inbox_counts[$group];
		}
		
		$users = ($group == 0)? array($this->user_id) : array();
		
		$message_options = array(
								 'group' => $groups,
								 'user' => $users,
								 );
		
		$messages = $this->vbx_message->get_messages($message_options,
													 $offset,
													 $max);
		$total_items = $messages['total'];
		$items = array();
		$this->load->library('pagination');
		$group_name = '';
		
		$data['active_users'] = VBX_User::search(array('is_active' => 1));
		
		if($messages['total'] < 1)
		{
			$group_name = $inbox_counts[$group]->name;
			
		}
		else
		{
			uasort($messages['messages'], 'sort_by_date');
			foreach($messages['messages'] as $item)
			{
				$group_name = '';
				$group_id = 0;
				if($item->owner_type == 'group' && isset($inbox_counts[$item->owner_id]))
				{
					$group_name = $inbox_counts[$item->owner_id]->name;
					$group_id = $item->owner_id;
				}
				if($item->owner_type == 'user' && isset($inbox_counts[$item->owner_id]))
				{
					$group_name = 'Inbox';
					$group_id = $item->owner_id;
				}
				
				$this->session->set_flashdata('selected-folder', $group_name);
				$this->session->set_flashdata('selected-folder-id', $group_id);

				$short_summary = null;
				
				if (is_null($item->content_text))
				{
					$short_summary = '&nbsp;';
					if ($do_transcriptions)
					{
						$short_summary = "(no transcription)";
					}
				}
				else
				{
					$short_summary = substr($item->content_text, 0, 125)
						 . ((strlen($item->content_text) > 125)? '...' : '');
				}
					
				$date_recorded = date('Y-M-d\TH:i:s+00:00', strtotime($item->created));
				$date_updated = date('Y-M-d\TH:i:s+00:00', strtotime($item->updated));

				$assigned_user = null;
				foreach($data['active_users'] as $u)
				{
					if($u->id == $item->assigned_to)
					{
						$assigned_user = clone($u);
					}
				}
				$items[] = array(
								 'id' => $item->id,
								 'folder' => $group_name,
								 'folder_id' => $group_id,
								 'short_summary' => $short_summary,
								 'assigned' => $item->assigned_to,
								 'type' => $item->type,
								 'assigned_user' => $assigned_user,
								 'ticket_status' => $item->ticket_status,
								 'archived' => ($item->status == 'archived')? true : false,
								 'unread' => ($item->status == 'new')? true : false,
								 'recording_url' => $item->content_url,
								 'recording_length' => format_player_time($item->size),
								 'received_time' => $date_recorded,
								 'last_updated' => $date_updated,
								 'called' => format_phone($item->called),
								 'caller' => format_phone($item->caller),
								 'original_called' => $item->called,
								 'original_caller' => $item->caller,
								 'owner_type' => $item->owner_type,
								 );

			}
			
			$group_name = 'Inbox';
		}
		
		// set up pagination
		$group_id = ($group === false)? 0 : $group;
		$page_config = array('base_url' => site_url('messages/inbox/' . $group_id),
							 'total_rows' => $total_items,
							 'per_page' => $max,
							 'uri_segment' => 4,
							 );
		$this->pagination->initialize($page_config);
		$data['items'] = $json['messages']['items'] = $items;
		// render to output array
		$data['pagination'] = CI_Template::literal($this->pagination->create_links());
		$data['transcribe'] = $do_transcriptions;

		/* Return current group */
		if($group !== false && $group >= 0)
		{
			// $json = $messages;
			$json['id'] = $group;
			$json['name'] = $inbox_counts[$group]->name;
			$json['read'] = $inbox_counts[$group]->read;
			$json['new'] = $inbox_counts[$group]->new;
			$json['messages']['total'] = $total_items;
			$json['messages']['offset'] = $offset;
			$json['messages']['max'] = $max;
		}
		else
		{
			/* Return folder summary */
			foreach($inbox_counts as $folder_count)
			{
				$folders[] = $folder_count;
			}
			$json = array(
				  'max' => $max,
				  'offset' => $offset,
				  'total' => $total_items,
				  'folders' => $folders,
				  );
		}

		$data['json'] = $json;
		$this->respond(' - '.$group_name, 'messages/inbox', $data);
	}

	// GET returns JSON; POST saves note and redirects
	function edit($message_id) {
		$message = $this->vbx_message->get_message($message_id);
		$notes = $this->input->post('notes');

		if($_POST) {
			if($message->exists()) {
				$message->notes = $notes;
				if(!$this->vbx_message->save($message)) {
					/* TODO: alert the user.. */
				}
			}
			redirect('messages');
		} else {
			// set message read flag
			$this->vbx_message->mark_read($message->id, $this->user_id);		
			$message->caller = format_phone($message->caller);
			$accepts = explode(',', $_SERVER['HTTP_ACCEPT']);
			if(in_array('application/json', $accepts))
			{
				echo json_encode($message);
				return;
			}

			return $this->details($message_id);
		}
	}

}