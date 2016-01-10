<?php

class Support extends Controller {
	public function rewrite()
	{
		header('status: 201');
		echo 'ok';
	}
}