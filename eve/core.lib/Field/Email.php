<?php

class Field_Email extends Field_String
{
	public function validate($data)
	{
		if (!filter_var($data, FILTER_VALIDATE_EMAIL))
		{
			return 'Email "' . $data . '" not valid';
		}
		return true;
	}
	
	public static function obfuscate($val)
	{
		return substr($val, 0, 1) . '...' . substr($val, strpos($val, '@'));
	}
}
