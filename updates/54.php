<?php
	/**
	 * Update tenants to include account type information
	 * Rev schema & application versions
	 *
	 * @return void
	 */
	function runUpdate_54() {
		$ci = &get_instance();
		$ci->settings->set('version', '1.0.5', 1);
		$ci->settings->set('schema-version', '54', 1);
	}