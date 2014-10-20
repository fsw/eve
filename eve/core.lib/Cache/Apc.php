<?php
/**
 * APC cache.
 * 
 * @package Core
 * @author fsw
 */

class Cache_Apc
{
	public static function set($key, $value, $ttl = 60)
	{
		Eve::startTimer('apc');
		if (Eve::useCache('apc') && function_exists('apc_store'))
		{
			apc_store($key, $value, $ttl);
		}
		Eve::stopTimer();
	}
	
	public static function get($key)
	{
		if (!Eve::useCache('apc') || !function_exists('apc_fetch'))
		{
			return null;
		}
		Eve::startTimer('apc');
		$ret = apc_fetch($key, $success);
		Eve::logEvent('apc', $key, $ret);
		Eve::stopTimer();
		return $success ? $ret : null;
	}
	
	public static function del($key)
	{
		Eve::startTimer('apc');
		if (Eve::useCache('apc') && function_exists('apc_delete'))
		{
			apc_delete($key);
		}
		Eve::stopTimer();
	}
	
}
