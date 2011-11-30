<?php

function runUpdate_64()
{
	$ci =& get_instance();
	$ci->vbx_settings->set('version', '1.1.3', 1);
	$ci->vbx_settings->set('schema-version', '64', 1);
}