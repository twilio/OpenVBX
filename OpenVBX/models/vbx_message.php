<?php
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

class VBX_MessageException extends Exception {}

/*
 * Message Class
 */
class VBX_Message extends Model {

	var $auto_populate_has_one = TRUE;
	var $has_one = array('group', 'user');

	var $table = 'messages';

	var $ticket_status_options = array('open', 'closed', 'pending');

	var $api_type = 'twilio';

	const TYPE_VOICE = 'voice';
	const TYPE_FAX = 'fax';
	const TYPE_SMS = 'sms';
	const TYPE_TELEPATHY = 'telepathy';

	const STATUS_NEW = 'new';
	const STATUS_READ = 'read';

	public function __construct()
	{
		parent::__construct();
	}

	function mark_read($message_id, $user_id)
	{
		$message = $this->get_message($message_id);
		$ci =& get_instance();

		if($message->status = self::STATUS_NEW)
		{
			$read_time = date('Y-m-d H:i:s');
			$ci->db->where('id', $message_id);
			$ci->db->where('messages.tenant_id', $ci->tenant->id);
			return $ci->db->update('messages',
									 array( '`read`' => $read_time,
											'status' => self::STATUS_READ));
		}

		if(!$this->has_read($message_id, $user_id))
		{
			$this->annotate($message_id, $user_id, 'Marked as read', 'read');
		}

		return false;
	}

	function assign($message_id, $user_id, $assignee)
	{
		$message = $this->get_message($message_id);
		if(!isset($assignee->id))
		{
			throw new VBX_MessageException('Unable to assign - user does not exist');
		}

		if($message->assigned_to == $assignee->id)
		{
			return false;

		}

		$message->assigned_to = $assignee->id;

		try
		{
			$this->save($message);
			$annotation_id = $this->vbx_message->annotate($message_id,
														  $user_id,
														  "Assigned to $assignee->email",
														  'changed');
			$annotations = $this->get_annotations($message_id);
			openvbx_mail($assignee->email,
						 "Message Assignment ({$message->owner}) {$message->caller}",
						 'message_assigned',
						 compact('message', 'annotations'));

		}
		catch(VBX_MessageException $e)
		{
			throw $e;
		}

	}

	function archive($message_id, $user_id, $archived)
	{
		$message = $this->get_message($message_id);
		$archived = boolean($archived);
		if(intval($message->archived) === $archived)
		{
			return false;
		}


		$message->archived = $archived;

		try
		{
			$this->save($message);
			$action = $message->archived? 'Archived' : 'Restored';
			$annotation_id = $this->vbx_message->annotate($message_id,
														  $user_id,
														  "$action message",
														  'archived');
		}
		catch(VBX_MessageException $e)
		{
			throw $e;
		}

	}

	function has_read($message_id, $user_id)
	{
		$annotations = $this->get_user_annotations($message_id, $user_id, 'read');
		if(!empty($annotations))
		{
			return true;
		}

		return false;
	}

	function ticket_status($message_id, $user_id, $ticket_status)
	{
		$message = $this->get_message($message_id);
		if(!in_array($ticket_status, $this->vbx_message->ticket_status_options))
		{
			throw new VBX_MessageException("Invalid Ticket Status: $ticket_status");
		}

		if($message->ticket_status == $ticket_status)
		{
			return false;
		}

		$message->ticket_status = $ticket_status;

		try
		{
			$this->save($message);
			$annotation_id = $this->vbx_message->annotate($message_id,
														  $user_id,
														  "Set ticket status to $ticket_status",
														  'changed');
		}
		catch(VBX_MessageException $e)
		{
			throw $e;
		}
	}

	function save($message, $notify = false)
	{
		$api_type = $message->api_type;

		$ci =& get_instance();

		if(isset($message->id) && intval($message->id) > 0)
		{
			$ci->db->trans_start();
			$result = $ci->db
				 ->set('messages.tenant_id', $ci->tenant->id)
				 ->set('updated', 'UTC_TIMESTAMP()', false)
				 ->set('content_text', $message->content_text)
				 ->set('content_url', $message->content_url)
				 ->set('notes', $message->notes)
				 ->set('caller', $message->caller)
				 ->set('called', $message->called)
				 ->set('size', $message->size)
				 ->set('type', $message->type)
				 ->set('call_sid', $message->call_sid)
				 ->set('status', $message->status)
				 ->set('ticket_status', $message->ticket_status)
				 ->set('assigned_to', $message->assigned_to)
				 ->set('archived', $message->archived)
				 ->where('id', $message->id)
				 ->update($this->table);
			$ci->db->trans_complete();
			$result = $ci->db->trans_status();
		} else {
			$ci->db->trans_start();
			$result = $ci->db
				 ->set('messages.tenant_id', $ci->tenant->id)
				 ->set('created', 'UTC_TIMESTAMP()', false)
				 ->set('updated', 'UTC_TIMESTAMP()', false)
				 ->set('content_text', @$message->content_text)
				 ->set('content_url', @$message->content_url)
				 ->set('notes', @$message->notes)
				 ->set('caller', $message->caller)
				 ->set('called', $message->called)
				 ->set('size', $message->size)
				 ->set('type', $message->type)
				 ->set('call_sid', $message->call_sid)
				 ->set('status', $message->status)
				 ->insert($this->table);

			$message->id = $ci->db->insert_id();

			if($message->owner_type == 'user')
			{
				$message_owner_table = 'user_messages';
				$message_owner = array('user_id' => $message->owner_id,
									   'message_id' => $message->id,
									   'tenant_id' => $ci->tenant->id);
			}

			if($message->owner_type == 'group')
			{
				$message_owner_table = 'group_messages';
				$message_owner = array('group_id' => $message->owner_id,
									   'message_id' => $message->id,
									   'tenant_id' => $ci->tenant->id);
			}

			$ci->db->insert($message_owner_table, $message_owner);
			$ci->db->trans_complete();

			$result = $ci->db->trans_status();
		}

		// refetch the message after persistence completed to update created, updated value
		$message = $this->get_message(array('call_sid' => $message->call_sid));
		/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
		// Hack to add api_type to message (isn't needed in DB)
		$message->api_type = $api_type;
		/** End Disruptive Technologies code **/

		if($result)
		{
			$this->notify_message($message, $notify);
		}

		if(!$result)
		{
			throw new VBX_MessageException('Unable to save message');
		}

		return $result;
	}

	function get_message($id)
	{
		$ci =& get_instance();

		$user_group_select = "IF(u.email IS NOT NULL , 'user', 'group') as owner_type, IF( u.email IS NOT NULL , u.email , g.name) as owner, IF (u.email IS NOT NULL, u.id, g.id) as owner_id";

		$ci->db->from($this->table)
			 ->select("messages.*, $user_group_select", false);
		if(is_array($id))
		{
			$ci->db->where($id);
		}
		else
		{
			$ci->db->where('messages.id', $id);
		}

		$result = $ci->db
			 ->select('messages.*, u.email, u.id as user_id, u.first_name, u.last_name')
			 ->join('group_messages gm', 'gm.message_id = messages.id', 'LEFT')
			 ->join('groups g', 'g.id = gm.group_id', 'LEFT')
			 ->join('user_messages um', 'um.message_id = messages.id', 'LEFT')
			 ->join('users u', 'u.id = um.user_id', 'LEFT')
			 ->where('messages.tenant_id', $ci->tenant->id)
			 ->get()->result();

		if(empty($result))
		{
			throw new VBX_MessageException("VBX_Message not found: message_id = $id");
		}

		return $result[0];
	}

	function get_messages_query($options)
	{
		$ci =& get_instance();
		$group = isset($options['group'])? $options['group'] : array();
		$user = isset($options['user'])? $options['user'] : array();
		$status = isset($options['status'])? $options['status'] : array();
		$call_sid = isset($options['call_sid'])? $options['call_sid'] : array();

		if(!empty($status))
		{
			$ci->db->where_not_in('messages.status', $status);
		}

		$group_sql = '';
		if(!empty($group))
		{
			$ci->db->join('group_messages gm', 'gm.message_id = messages.id', 'LEFT');
			$ci->db->join('groups g', 'g.id = gm.group_id', 'LEFT');
			$group_sql = 'gm.group_id IN ('.implode(',', array_map('intval', $group)).')';
		}

		$user_sql = '';
		if(!empty($user))
		{
			$ci->db->join('user_messages um', 'um.message_id = messages.id', 'LEFT');
			$ci->db->join('users u', 'u.id = um.user_id', 'LEFT');
			$user_sql = 'um.user_id IN ('.implode(',', array_map('intval', $user)).')';
		}

		if(!empty($user_sql) || !empty($group_sql))
		{
			$user_sql = '('. $user_sql . (!empty($user_sql)? ' OR ' : '') . $group_sql .')';
		}

		$user_group_select = '1=1';
		if(!empty($user) && !empty($group))
		{
			$user_group_select = "IF(u.email IS NOT NULL , 'user', 'group') as owner_type, IF( u.email IS NOT NULL , u.email , g.name) as owner, IF (u.email IS NOT NULL, u.id, g.id) as owner_id";
		}
		else if(empty($user) && !empty($group))
		{
			$user_group_select = "'group' as owner_type, g.name as owner, g.id as owner_id";
		}
		else if(!empty($user) && empty($group))
		{
			$user_group_select = "'user' as owner_type, u.email as owner, u.id as owner_id";
		}

		if(!empty($call_sid))
		{
			$ci->db->where_in('call_sid', $call_sid);
		}

		if(!empty($user_sql))
		{
			$ci->db->where($user_sql);
		}

		$ci->db
			 ->where('messages.tenant_id', $ci->tenant->id)
			 ->where('archived', false);

		// Only show messages that have been transcribed OR if they're older than 5 minutes because
		// at that point, we can assume the transcription isn't coming.
		// Disabled for now
		// $ci->db
		     // ->where('( (content_text IS NOT NULL) OR ( UTC_TIMESTAMP() >= DATE_ADD(created, INTERVAL 5 MINUTE) ) )');

		$ci->db
			 ->select("messages.*, $user_group_select", false)
			 ->order_by('messages.created DESC')
			 ->from($this->table);

		return $ci->db;
	}

	function get_messages($options, $offset, $size)
	{
		$query = $this->get_messages_query($options);
		$result['total'] = $query->count_all_results();
		$result['max'] = $size;
		$result['offset'] = $offset;
		// $query = $this->get_messages_query($options);
		$query = $this->get_messages_query($options);
		$result['messages'] = $query
			 ->limit($size, $offset)
			 ->get()
			 ->result();

		return $result;
	}

	function notify_message($message, $notify = false)
	{
		error_log("Notify message: ".($notify? 'true' : 'false') );
		if($notify === false)
			return;

		$ci =& get_instance();
		$ci->load->model('vbx_user');
		$ci->load->model('vbx_group');

		$users = array();
		if($message->owner_type == 'user')
		{
			$user = VBX_User::get($message->owner_id);
			if(!empty($user->email))
			{
				$notify[] = $user->email;
			}
		}

		$group_users = array();
		if($message->owner_type == 'group')
		{
			$user_ids = $ci->vbx_group->get_user_ids($message->owner_id);
			$group = $ci->vbx_group->get_by_id($message->owner_id);
			$owner = $group->name;
		}
		else if($message->owner_type == 'user')
		{
			$user_ids = array($message->owner_id);
			$owner = 'Personal';
		}

		foreach($user_ids as $user_id)
		{
			$user = VBX_User::get($user_id);
			$ci->load->model('vbx_device');
			$ci->load->model('vbx_sms_message');
			$numbers = VBX_Device::search(array('user_id' => $user_id));
			$message_type = 'Voicemail';
			if($message->type == 'sms')
			{
				$message_type = 'SMS';
				$owner = '';
			}


			if($message->type == 'voice')
			{
				openvbx_mail($user->email,
							 "New $owner $message_type Notification - {$message->caller}",
							 'message',
							 compact('message'));
				error_log("message queued for $email");
			}

			foreach($numbers as $number)
			{
				error_log($number->value);
				error_log(var_export($number->values, true));
				if($number->value && $number->sms)
				{
					try
					{
						switch ($message->api_type) {
							case 'twilio':
								$ci->vbx_sms_message->send_message(
									$message->called,
									$number->value,
									$this->tiny_notification_message($message)
									);
								break;
							case 'tropo':
								$ci->vbx_sms_message->send_message(
									$message->called,
									$number->value,
									$this->tiny_notification_message($message),
									'tropo'
									);
								break;

							default:
								throw new Exception("Invalid message API type.");
								break;
						}
						error_log("sms queued for {$number->value}");
					}
					catch(Sms_messageException $e)
					{
						error_log("unable to send sms alert, reason: ".$e->getMessage());
					}
				}
			}
		}
	}

	function tiny_notification_message($message)
	{
		switch($message->type)
		{
			case 'sms':
				$content = $message->caller . ':'. $message->content_text;
				$content = substr($content, 0, 159);
				break;
			case 'voice':
				$content = "New Voicemail from {$message->caller}\n\n";
				break;
		}


		return $content;
	}

	function message_owner()
	{
		$group = new Group();
		$group->get_by_id($this->group_id);
		if($group->name)
			return $group->name;

		$user = new User();
		$user->get_by_id($this->user_id);
		return $user->full_name();
	}

	function get_folders($user_id, $group_ids)
	{
		$folders = array();
		$status_fields = array( 'archived' => 0, 'new' => 0, 'read' => 0, 'total' => 0);

		$ci =& get_instance();

		$user_message_count = $ci->db
			 ->select('status, count(m.status) as count')
			 ->from('messages m')
			 ->join('user_messages um', 'um.message_id = m.id')
			 ->where('um.user_id', $user_id)
			 ->where('m.tenant_id', $ci->tenant->id)
			 ->where('m.archived', 0)
			 ->group_by('status')
			 ->get()->result();

		$user_message_total = $ci->db
			 ->select('count(m.status) as count')
			 ->from('messages m')
			 ->join('user_messages um', 'um.message_id = m.id')
			 ->where('um.user_id', $user_id)
			 ->where('m.tenant_id', $ci->tenant->id)
			 ->where('m.archived', 0)
			 ->get()->result();

		$inbox_id = 0;
		$folders[$inbox_id]->id = $inbox_id;
		$folders[$inbox_id]->name ='Inbox';
		$folders[$inbox_id]->type = 'inbox';

		foreach($status_fields as $status_key => $status_value)
		{
			$folders[$inbox_id]->{$status_key} = $status_value;
		}

		foreach($user_message_count as $c)
		{
			$folders[$inbox_id]->{$c->status} = $c->count;
		}

		if(isset($user_message_total[0]))
			$folders[$inbox_id]->total = $user_message_total[0]->count;

		if(!empty($group_ids))
		{
			$groups = $ci->db
				->from('groups g')
				->where_in('g.id', $group_ids)
				->get()->result();

			/* Initialize groups */
			foreach($groups as $i => $g)
			{
				$group_id = intval($g->id);
				$folders[$group_id] = $g;
				foreach($status_fields as $status_key => $status_value)
				{
					$folders[$group_id]->{$status_key} = $status_value;
				}

				$folders[$group_id]->type = 'group';
			}

			$group_status_counts = $ci->db
				 ->select('g.name, status, g.id, count(m.status) as count')
				 ->from('groups g')
				 ->join('group_messages gm', 'gm.group_id = g.id')
				 ->join('messages m', 'm.id = gm.message_id')
				 ->where_in('gm.group_id', $group_ids)
				 ->where('archived', false)
				 ->where('m.tenant_id', $ci->tenant->id)
				 ->group_by('m.status, g.id')
				 ->get()->result();

			$group_folder_totals = array();
			foreach($group_status_counts as $status_count)
			{
				$folders[intval($status_count->id)]->{$status_count->status} = $status_count->count;
				$folders[$inbox_id]->{$status_count->status} += $status_count->count;
				if(!isset($group_folder_totals[intval($status_count->id)]))
				{
					$group_folder_totals[intval($status_count->id)] = $status_count->count;
				}
				else
				{
					$group_folder_totals[intval($status_count->id)] += $status_count->count;
				}
			}

			foreach($group_folder_totals as $i => $total)
			{
				$folders[intval($i)]->total = $total;
				$folders[$inbox_id]->total += $total;
			}
		}

		return $folders;
	}

	function get_annotations($message_id)
	{
		return $this->get_message_annotations($message_id);

	}

	function get_annotation($annotation_id)
	{
		$ci =& get_instance();

		$annotation = $ci->db
			->select('u.email, u.id as user_id, u.first_name, u.last_name, a.*, at.description as annotation_type')
			->from('annotations a')
			->join('users u', 'u.id = a.user_id')
			->join('annotation_types at', 'at.id = a.annotation_type')
			->where('a.id', $annotation_id)
			->where('a.tenant_id', $ci->tenant->id)
			->order_by('a.created DESC')
			->get()->result();

		if(!empty($annotation))
		{
			$annotation = $annotation[0];
		}

		return $annotation;
	}

	function get_user_annotations($message_id, $user_id, $annotation_type = null)
	{
		$ci =& get_instance();

		$user_annotations = $ci->db
			->select('a.*, u.email, u.id as user_id, u.first_name, u.last_name')
			->from('annotations a')
			->join('annotation_types at', 'at.id = a.annotation_type')
			->join('users u', 'u.id = a.user_id')
			->where('a.message_id', $message_id)
			->where('a.tenant_id', $ci->tenant->id)
			->where('a.user_id', $user_id);

		if($annotation_type)
		{
			$ci->db
				->where('at.description', $annotation_type);
		}

		$ci->db
			 ->get()->result();

		return $user_annotations;
	}

	function get_message_annotations($message_id, $annotation_type = null)
	{
		$ci =& get_instance();

		$message_annotations = $ci->db
			->select('a.*, u.email, u.id as user_id, u.first_name, u.last_name, at.description as annotation_type')
			->from('annotations a')
			->join('annotation_types at', 'at.id = a.annotation_type')
			->join('users u', 'u.id = a.user_id')
			->order_by('a.created DESC')
			->where('a.message_id', $message_id)
			->where('a.tenant_id', $ci->tenant->id)
			->get()->result();

		return $message_annotations;
	}

	function annotate($message_id, $user_id, $description, $annotation_type)
	{
		$ci =& get_instance();

		if(!($annotation_type = $this->get_annotation_type($annotation_type)))
		{
			return false;
		}

		$ci->db
			 ->set('message_id', $message_id)
			 ->set('user_id', $user_id)
			 ->set('description', $description)
			 ->set('annotation_type', $annotation_type)
			 ->set('created', 'UTC_TIMESTAMP()', false)
			 ->set('tenant_id', $ci->tenant->id)
			 ->insert('annotations');
		return $ci->db->insert_id();
	}

	function get_annotation_type($annotation_type)
	{
		$ci =& get_instance();

		$annotation = $ci->db
			->from('annotation_types')
			->where('description', $annotation_type)
			->or_where('id', $annotation_type)
			->get()->result();

		if(!empty($annotation))
		{
			$annotation = $annotation[0]->id;
		}

		return $annotation;
	}

}

