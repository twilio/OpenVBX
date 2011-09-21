<?php

function runUpdate_56() {
	$ci =& get_instance();
	$ci->vbx_settings->set('version', '1.1b3', 1);
	$ci->vbx_settings->set('schema-version', '56', 1);
}
