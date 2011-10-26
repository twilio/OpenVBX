<?php
	/* runUpdate_49() updates version, schema and adds field for `application_sid` to each tenant */
	function runUpdate_49() {
		$ci = &get_instance();
		
		$ci->settings->add('application_sid', '', 1);
				
		if (!$ci->db->field_exists('online', 'users')) {
			$ci->load->dbforge();
			$ci->dbforge->add_column('users', array(
				'online' => array(
					'type' => 'TINYINT',
					'constraint' => '1',
					'default' => '9'
				)
			));
		}
		
		$ci->settings->set('version', '0.93', 1);
		$ci->settings->set('schema-version', '49', 1);
	}
?>