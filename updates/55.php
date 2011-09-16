<?php

function runUpdate_55() {
	$ci =& get_instance();
	$ci->vbx_settings->set('version', '1.1b2', 1);
	$ci->vbx_settings->set('schema-version', '55', 1);
}
