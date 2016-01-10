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
	
class VBX_Plugin_StoreException extends Exception {}

/**
 * Class VBX_Plugin_Store
 * @property string $key
 * @property string $value
 * @property int $plugin_id
 */
class VBX_Plugin_Store extends MY_Model
{
	protected static $__CLASS__ = __CLASS__;
	public $table = 'plugin_store';

	static public $joins = array();
	static public $select = array(
		'plugin_store.`key`, plugin_store.`value`, plugin_store.`plugin_id`'
	);

	public $error_prefix = '';
	public $error_suffix = '';
		
	public $fields = array(
						'key', 
						'value', 
						'plugin_id'
					);
					
	public $natural_keys = array(
								'key', 
								'plugin_id'
							);
	
	function __construct($object = null)
	{
		parent::__construct($object);
	}
	
	static function get($search_options = array(), $limit = -1, $offset = 0)
	{
		if(empty($search_options))
		{
			return null;
		}

		if(!(isset($search_options['key']) && isset($search_options['plugin_id'])))
		{
			throw new VBX_PluginException('VBX_Plugin_Store requires key and plugin_id '.
											'arguments for VBX_Plugin_Store::get()');
		}
		
		return self::search($search_options, 1, 0);
	}
	
	static function search($search_options = array(), $limit = -1, $offset = 0)
	{
		$sql_options = array(
			'joins' => self::$joins,
			'select' => self::$select,
		);
		$store = new self();
		
		$values = parent::search(
			self::$__CLASS__,
			$store->table,
			$search_options,
			$sql_options,
			$limit,
			$offset
		);

		return $values;
	}

	public function __get($name)
	{
		if ($name == 'id')
		{
			return $this->_id();
		}
		else {
			return parent::__get($name);
		}
	}
	
	/**
	 * This is a haxie to get caching to work properly with PluginData.
	 * Caching stores items by ID but PluginData objects have a compound
	 * id, not a unique auto-increment id like everything else.
	 *
	 * @return string
	 */
	protected function _id()
	{
		return $this->values['key'].$this->values['plugin_id'];
	}
}