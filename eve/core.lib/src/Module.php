<?php
/**
 * Module.
 * 
 * @package Framework
 * @author fsw
 */

abstract class Module
{
	public function getModules()
	{
		return array();
	}
	
	public static function getModuleCode()
	{
		return lcfirst(str_replace('module_', '', get_called_class()));
	}
	
	public static function getConfigFields()
	{
		return array();
	}
	
	public static function getConfig($key = null)
	{
		$configs = cache_Array::get('configs');
		if ($configs === null)
		{
			$configs = array();
			foreach (Eve::getDescendants('Module') as $module)
			{
				$code = $module::getModuleCode(); 
				$configs[$code] = Config::get($code);
			}
			$rows = model_Configs::getAll();
			foreach ($rows as $row)
			{
				$config = array();
				if (!empty($configs[$row['key']])){
					$config = $configs[$row['key']];
				}
				$config = array_merge($config, $row['value']);
				$configs[$row['key']] = $config;
			}
			cache_Array::set('configs', $configs);
		}
		
		$config = array_key_exists(static::getModuleCode(), $configs) ? $configs[static::getModuleCode()] : array();
		if (empty($key))
		{
			return $config;
		}
		if (!empty($config[$key]))
		{
			return $config[$key];
		}
		return null;
	}
	
	public static function saveConfig($config)
	{
		$current = model_Configs::getByField('key', static::getModuleCode());
		if (empty($current))
		{
			$current = array();
		}
		$current['key'] = static::getModuleCode();
		$current['value'] = $config;
		return model_Configs::save($current);
	}
}
