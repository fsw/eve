<?php
/** 
 * Common text processing functions
 * 
 * @package Core
 * @author fsw
 */

class Text
{
	public static function excerpt($text, $maxLength = 100)
	{
		return mb_strlen($text) > $maxLength ? mb_substr($text, 0, $maxLength) . '...' : $text;
	}

	public static function slug($text)
	{
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
		$clean = str_replace('&', ' and ', $clean);
		$clean = preg_replace("/[^a-zA-Z0-9]/", '-', $clean);
		$clean = str_replace('--', '-', $clean);
		$clean = str_replace('--', '-', $clean);
		$clean = strtolower(trim($clean, '-'));
		return empty($clean) ? 'slug' : $clean;
	}
	
	public static function formatSize($bytes, $si = false, $format = null)
	{
		$format = ($format === null) ? '%01.2f %s' : $format;
		
		if ($si == false)
		{
			$units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
			$mod = 1024;
		}
		else
		{
			$units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
			$mod = 1000;
		}
		//TODO fix this crap:
		$power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
		return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
	}
	
	public static function deBBCode($text)
	{
		$text = preg_replace('/\n/i', '<br />', $text);
		$text = preg_replace('/\[b\]/i', '<strong>', $text);
		$text = preg_replace('/\[\/b\]/i', '</strong>', $text);
		$text = preg_replace('/\[i\]/i', '<em>', $text);
		$text = preg_replace('/\[\/i\]/i', '</em>', $text);
		$text = preg_replace('/\[u\]/i', '<u>', $text);
		$text = preg_replace('/\[\/u\]/i', '</u>', $text);
		$text = preg_replace('/\[url=([^\]]+)\](.*?)\[\/url\]/i', '<a href="$1">$2</a>', $text);
		$text = preg_replace('/\[url\](.*?)\[\/url\]/i', '<a href="$1">$1</a>', $text);
		$text = preg_replace('/\[img\](.*?)\[\/img\]/i', '<img src="$1" />', $text);
		$text = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/i', '<font color="$1">$2</font>', $text);
		$text = preg_replace('/\[code\](.*?)\[\/code\]/i', '<span class="codeStyle">$1</span>&nbsp;', $text);
		$text = preg_replace('/\[quote.*?\](.*?)\[\/quote\]/i', '<span class="quoteStyle">$1</span>&nbsp;', $text);
		return $text;
	}
}