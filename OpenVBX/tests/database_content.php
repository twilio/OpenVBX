<?php
/**
 * Database defaults for initial install
 * Volatile data should be managed via the fixtures/*_fixt.yml files
 */
$database_content = array(
	'annotation_types' => array(
	),
	
	'annotations' => array(
	),
	
	'audio_files' => array(
	),
	
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

	'groups' => array(
	),
	
	'groups_users' => array(	
	),

	'messages' => array(	
	),

	'numbers' => array(
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

	'tenants' => array(
	),

	'user_annotations' => array(	
	),
	
	'user_messages' => array(	
	),
	
	'users' => array(
	)
);
