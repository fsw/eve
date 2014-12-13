<?php

class Config
{
	private static $config = array();
	private static $initialized = false;
	
	public static function get($class, $key = null, $default = null)
	{
		if (!self::$initialized)
		{
			self::initialize();
		}
		if (is_null($key))
		{
			return array_key_exists($class, self::$config) ? self::$config[$class] : [];
		}	
		else
		{
			if (array_key_exists($class, self::$config) && array_key_exists($key, self::$config[$class]))
			{
				return self::$config[$class][$key];
			}
			else
			{
				return $default;
			}
		}
	}
	
	private static function initialize()
	{
		$configCachePath = Eve::getCacheDir() . 'config.php';
		if (Eve::useCache('core') && file_exists($configCachePath))
		{
			require $configCachePath;
		}
		else
		{
			$paths = array_reverse(Eve::findAll('config.php'));
			if (file_exists('config.php'))
			{
				$paths[] = 'config.php';
			}
			foreach ($paths as $path)
			{
				require $path;
				self::$config = array_merge_recursive(self::$config, $config);
			}
			if (Eve::useCache('core'))
			{
				$contents = '<?php' . NL . 'self::$config=' . var_export(self::$config, true) . ';' . NL;
				file_put_contents($configCachePath, $contents);
			}
		}
		self::$initialized = true;
	}
}
