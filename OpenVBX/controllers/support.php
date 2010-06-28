<?php

class Support extends Controller {
	public function __construct()
	{
		parent::__construct();
	}

	public function rewrite()
	{
		header('status: 201');
		echo 'ok';
	}
}