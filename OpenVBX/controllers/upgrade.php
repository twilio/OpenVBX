<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 **/

require_once APPPATH. '/libraries/OpenVBX.php';

class UpgradeException extends Exception {}

class Upgrade extends User_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->section = 'upgrade';
		$this->admin_only($this->section);
	}
	
	public function index()
	{
		$currentSchemaVersion = OpenVBX::schemaVersion();
		$upgradingToSchemaVersion = OpenVBX::getLatestSchemaVersion();
		if($currentSchemaVersion == $upgradingToSchemaVersion)
			redirect('/');
		
		$this->load->view('upgrade/main');
	}

	public function validate()
	{
		$step = $this->input->post('step');
		$json = array('success' => true);
		if($step == 1) {
			echo json_encode($json);
			return;
		}
		
		$tplvars = $this->input_args();
		switch($step)
		{
			case 2:
				$json = $this->validate_step2();
				break;
		}
		
		$json['tplvars'] = $tplvars;
		echo json_encode($json);
	}

	private function input_args()
	{
		$tplvars = array();

		return $tplvars;
	}
	
	public function setup()
	{
		$json['success'] = true;
		$json['message'] = '';
		
		try
		{
			$currentSchemaVersion = OpenVBX::schemaVersion();
			$upgradingToSchemaVersion = OpenVBX::getLatestSchemaVersion();

			$sqlPath = VBX_ROOT.'/sql-updates/';
			$updates = scandir($sqlPath);
			$files = array();
			foreach($updates as $i => $update)
			{
				if(preg_match('/^(\d+).sql$/', $update) )
				{
					$rev = intval(str_replace('.sql', '', $update));
					$files[$rev] = $update;
				}
			}

			ksort($files);
			$files = array_slice($files, $currentSchemaVersion);
			$tplvars = array('originalVersion' => $currentSchemaVersion,
							 'version' => $upgradingToSchemaVersion,
							 'updates' => $files );
			
			foreach($files as $file)
			{
				$sql = @file_get_contents($sqlPath.$file);
				if(empty($sql))
				{
					throw new UpgradeException("Unable to read update: $file", 1);
				}

				foreach(explode(";", $sql) as $stmt)
				{
					$stmt = trim($stmt);
					if(!empty($stmt))
					{
						PluginData::sqlQuery($stmt);
					}
				}
			}
			
		} catch(Exception $e) {
			$json['success'] = false;
			$json['message'] = $e->getMessage();
			$json['step'] = $e->getCode();
		}
			  
		$json['tplvars'] = $tplvars;
		echo json_encode($json);
	}
}
