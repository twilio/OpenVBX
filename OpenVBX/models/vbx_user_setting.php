<?php

class VBX_User_SettingException extends Exception {}
class VBX_User_Setting extends MY_Model {	
	public $table = 'user_settings';

	public $fields = array(
		'id',
		'user_id',
		'key',
		'value'
	);

	public function __construct($object = null)
	{
		parent::__construct($object);
	}
	
	public static function get($key, $user_id)
	{
		$ci =& get_instance();
		
		if ($cache = $ci->cache->get($key, 'user-setting-'.$user_id, $ci->tenant->id))
		{
			return $cache;
		}
		
		$model = new VBX_User_Setting;
		
		$result = $ci->db
			->from($model->table)
			->where(array(
				'user_id' => $user_id,
				'key' => $key,
				'tenant_id' => $tenant_id
			))
			->get()->result();
		
		$setting = false;
		if (!empty($result[0]))
		{
			$setting = new VBX_User_Setting($result[0]);
			$ci->cache->set($setting->key, $setting, 'user-setting-'.$user_id, $ci->tenant->id);
		}
		
		return $setting;
	}
	
	public static function get_by_user($user_id)
	{
		$ci =& get_instance();
		$model = new VBX_User_Setting;
		
		$result = $ci->db
			->from($model->table)
			->where(array(
				'user_id' => $user_id,
				'tenant_id' => $ci->tenant->id
			))
			->get()->result();
			
		if (!empty($result))
		{
			foreach ($result as &$setting)
			{
				$setting = new VBX_User_Setting($setting);
				$ci->cache->set($setting->key, $setting, 'user-setting-'.$user_id, $ci->tenant->id);
			}
		}

		return $result;
	}
}