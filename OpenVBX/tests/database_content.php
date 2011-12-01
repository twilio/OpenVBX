<?php

$database_content = array(
	// default set added from openvbx.sql
	'annotation_types' => array(
	),
	
	'annotations' => array(
	),
	
	'audio_files' => array(
	),
	
	// default set added in openvbx.sql
	'auth_types' => array(
	),
	
	'flow_store' => array(
	),
	
	'flows' => array(	
	),

	'group_annotations' => array(	
	),
	
	'group_messages' => array(	
	),

	// default set added in openvbx.sql	
	'groups' => array(
	),
	
	'groups_users' => array(	
	),

	'messages' => array(	
	),

	'numbers' => array(
		array(
			'id' => '',
			'user_id' => 1,
			'name' => 'My Device',
			'value' => '+14151112222',
			'is_active' => 1,
			'sms' => 1,
			'sequence' => null,
			'tenant_id' => 1
		)
	),

	'plugin_store' => array(	
	),

	'rest_access' => array(	
	),

	// override or add to default set added in openvbx.sql
	'settings' => array(
		array(
			'id' => '',
			'tenant_id' => 1,
			'name' => 'from_email',
			'value' => 'admin@openvbx.local'
		),
		array(
			'id' => '',
			'tenant_id' => 1,
			'name' => 'twilio_sid',
			'value' => TWILIO_SID
		),
		array(
			'id' => '',
			'tenant_id' => 1,
			'name' => 'twilio_token',
			'value' => TWILIO_TOKEN
		)
	),

	// default tenant 1 added from openvbx.sql
	'tenants' => array(
	),

	'user_annotations' => array(	
	),
	
	'user_messages' => array(	
	),
	
	'users' => array(
		array(
			'id' => 1,
			'is_admin' => 1,
			'is_active' => 1,
			'first_name' => 'admin',
			'last_name' => 'user',
			'password' => 'password', // will be hashed on insert
			'invite_code' => NULL,
			'email' => 'admin@openvbx.local',
			'pin' => NULL,
			'notification' => NULL,
			'auth_type' => 1,
			'voicemail' => 'I am user number 1. I am not at home. Leave me a message.',
			'tenant_id' => 1,
			'last_seen' => NULL,
			'last_login' => NULL,
			'online' => 0
		)	
	)
);
