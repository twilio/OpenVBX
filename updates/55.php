<?php

function runUpdate_55() {
	$ci->settings->set('version', '1.1b2', 1);
	$ci->settings->set('schema-version', '55', 1);
}
