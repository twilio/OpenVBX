<?php
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
	
define('AUDIOPATH', dirname(FCPATH) . '/audio-uploads/');

/**
 * Class VBX_Audio_File
 * @property int $id
 * @property string $label
 * @property int $user_id
 * @property string $url
 * @property string $recording_call_sid
 * @property string $tag
 * @property int $cancelled
 * @property string $created
 * @property string $updated
 */
class VBX_Audio_File extends MY_Model
{
	protected static $__CLASS__ = __CLASS__;
	public $table = 'audio_files';
	public $fields = array(
						'id',
						'label',
						'user_id',
						'url',
						'recording_call_sid',
						'tag',
						'cancelled',
						'created',
						'updated'
					);

	public function __construct($object = null)
	{
		parent::__construct($object);
	}
	
	static function get($search_options = array())
	{
		if(empty($search_options))
		{
			return null;
		}

		if(is_numeric($search_options))
		{
			$search_options = array('id' => $search_options);
		}

		return self::search($search_options, 1, 0);
	}
	
	static function search($search_options = array(), $limit = -1, $offset = 0)
	{
		$sql_options = array();
							
		$instance = new self();
		
		$values = parent::search(
			self::$__CLASS__,
			$instance->table,
			$search_options,
			$sql_options,
			$limit,
			$offset
		);

		return $values;
	}

	public function save()
	{
		$now_in_mysql_format = date('Y-m-d H:i:s');

		$this->updated = $now_in_mysql_format;
		
		if (!($this->id > 0))
		{
			$this->created = $now_in_mysql_format;
		}

		return parent::save();
	}
}