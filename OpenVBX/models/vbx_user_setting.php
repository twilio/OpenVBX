<?php

class VBX_User_SettingException extends Exception {}

/**
 * User settings Object
 * Does not cache due to update frequency
 * @todo - enable caching based on key?
 *
 * @property int $id
 * @property int $user_id
 * @property string $key
 * @property string $value
 * @property int $tenant_id
 */
class VBX_User_Setting extends MY_Model {	
	public static $caching = false;
	protected static $__CLASS__ = __CLASS__;
	public $table = 'user_settings';

	public $fields = array(
		'id',
		'user_id',
		'key',
		'value', 
		'tenant_id'
	);
	
	public function __construct($object = null)
	{
		parent::__construct($object);
	}
	
	public static function get($key, $user_id)
	{
		$search_opts = array(
			'user_id' => $user_id
		);

		if (is_numeric($key))
		{
			$search_opts['id'] = intval($key);
		}
		else {
			$search_opts['key'] = $key;
		}
		
		return self::search($search_opts, 1);
	}
	
	public static function get_by_user($user_id)
	{
		$search_opts = array(
			'user_id' => $user_id
		);
		return self::search($search_opts);
	}
	
	public static function search($search_options, $limit = -1, $offset = 0)
	{
		$setting_object = new self::$__CLASS__;
		
		$settings = parent::search(
			self::$__CLASS__,
			$setting_object->table,
			$search_options,
			array(),
			$limit,
			$offset
		);
				
		return (!empty($settings) ? $settings : false);
	}	
}