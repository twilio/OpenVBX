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
		
		// no cache
		$ci =& get_instance();
		$ci->cache->flush();
		$ci->cache->enabled(false);
	}

	public function index()
	{
		$currentSchemaVersion = OpenVBX::schemaVersion();
		$upgradingToSchemaVersion = OpenVBX::getLatestSchemaVersion();
		if($currentSchemaVersion == $upgradingToSchemaVersion)
		{
			redirect('/');
		}
		
		$plugins = Plugin::all();
		foreach($plugins as $plugin)
		{
			$data['plugins'][] = $plugin->getInfo();
		}

		$data['php_version_min'] = MIN_PHP_VERSION;
		$data['php_version'] = phpversion();
		if (!version_compare($data['php_version'], $data['php_version_min'], '>=')) 
		{
			$data['error'] = $this->load->view('upgrade/min-php-req-notice', $data, true);
		}
		$this->load->view('upgrade/main', $data);
	}

	/**
	 * There's no validation done during upgrade
	 * This method is not necessarily deprecated, just unused... 
	 * Reserving the right to use it in the future.
	 *
	 * @return void
	 */
	public function validate()
	{
		$step = $this->input->post('step');
		$json = array('success' => true);
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

			$upgradeScriptPath = VBX_ROOT.'/updates/';
			$updates = scandir($upgradeScriptPath);
			$updatesToRun = array();
			// Collect all files named numerically in /updates and key sort the list of updates
			foreach($updates as $i => $update)
			{
				if(preg_match('/^(\d+).(sql|php)$/', $update, $matches) )
				{
					$updateExtension = $matches[2];
					$rev = $matches[1];
					$updatesToRun[$rev] = array( 
						'type' => $updateExtension,
						'filename' => $update,
						'revision' => $rev,
					);
				}
			}

			ksort($updatesToRun);

			// Cut the updates by the current schema version.
			$updatesToRun = array_slice($updatesToRun, $currentSchemaVersion);
			$tplvars = array(
				'originalVersion' => $currentSchemaVersion,
				'version' => $upgradingToSchemaVersion,
				'updates' => $updatesToRun
			);

			foreach($updatesToRun as $updateToRun)
			{
				$file = $updateToRun['filename'];
				$type = $updateToRun['type'];
				$revision = $updateToRun['revision'];
				switch($type) 
				{
					case 'php':
						require_once($upgradeScriptPath.$file);
						$runUpdateMethod = "runUpdate_$revision";
						if(!function_exists($runUpdateMethod))
						{
							throw(new UpgradeException('runUpdate method missing from '.
														$file.': '.$runUpdateMethod));
						}
						call_user_func($runUpdateMethod);
						break;
					case 'sql':
						$sql = @file_get_contents($upgradeScriptPath.$file);
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
						break;
				}
			}
			flush_minify_caches();
		} 
		catch(Exception $e) {
			$json['success'] = false;
			$json['message'] = $e->getMessage();
			$json['step'] = $e->getCode();
		}

		$json['tplvars'] = $tplvars;
		echo json_encode($json);
	}
}
