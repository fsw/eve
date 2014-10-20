<?php 
/**
 * arrays helper
 */
class Arr
{
	public static function insertAfter(array &$array, $key, $newKey, $newValue)
	{
		$new = array();
		foreach ($array as $k => $value)
		{
			$new[$k] = $value;
			if ($k === $key)
			{
				$new[$newKey] = $newValue;
			}
		}
		$array = $new;
	}
	
	public static function insertBefore(array &$array, $key, $newKey, $newValue)
	{
		$new = array();
		foreach ($array as $k => $value)
		{
			if ($k === $key)
			{
				$new[$newKey] = $newValue;
			}
			$new[$k] = $value;
		}
		$array = $new;
	}
	
	public static function isAssoc(array $array)
	{
		return (bool)count(array_filter(array_keys($array), 'is_string'));
	}
}