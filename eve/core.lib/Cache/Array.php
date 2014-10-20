<?php
/**
 * Array cache.
 * 
 * @package Core
 * @author fsw
 */

class Cache_Array
{
	private static $cache;
	
	public static function set($key, $value)
	{
		Eve::startTimer('arraycache');
		if (Eve::useCache('array'))
		{
			$path = Eve::getCacheDir() . 'arraycache' . DS . $key . '.php';
			static::$cache[$key] = $value;
			Fs::rwrite($path, '<?php' . NL . '$ret=' . var_export($value, true) . ';' . NL);
		}
		Eve::stopTimer();
	}
	
	public static function get($key)
	{
		Eve::startTimer('arraycache');
		$path = Eve::getCacheDir() . 'arraycache' . DS . $key . '.php';
		if (!Eve::useCache('array'))
		{
			$ret = null;
		}
		elseif (!empty(static::$cache[$key]))
		{
			$ret = static::$cache[$key];
		}
		elseif (file_exists($path))
		{
			include $path;
			static::$cache[$key] = $ret;
		}
		else
		{
			$ret = null;
		}
		Eve::logEvent('arraycache', $key, $ret);
		Eve::stopTimer();
		return $ret;
	}
	
	public static function del($key)
	{
		Eve::startTimer('arraycache');
		if (Eve::useCache('array'))
		{
			$path = Eve::getCacheDir() . 'arraycache' . DS . $key . '.php';
			Fs::rremove($path);
			unset(static::$cache[$key]);
		}
		Eve::stopTimer();
	}
	
}
