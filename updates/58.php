<?php

function runUpdate_58() {
	$ci =& get_instance();
	
	if (!$ci->db->field_exists('order', 'groups_users')) {
		$ci->load->dbforge();
		$ci->dbforge->add_column('groups_users', array(
			'order' => array(
				'type' => 'TINYINT',
				'constraint' => '3',
				'default' => '0'
			)
		));
	}
	
	$ci->vbx_settings->set('schema-version', '58', 1);
}