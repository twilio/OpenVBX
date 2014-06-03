<?php

// We're making a call using the Services_Twilio library and 
// using the twilio credentials for this VBX instalation
$account = OpenVBX::getAccount();

// Set a limit of items per page
$limit = 50;
	
// Get the page of calls from Twilio
$calls = $account->calls->getPage(0, $limit, array())->getItems();