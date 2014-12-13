<?php
/**
 * Site.
 * 
 * @package Framework
 * @author fsw
 */

abstract class BaseSite extends Module
{	
	protected static $request = null;
	
	private static $controller = null;
	private static $method = null;
	private static $args = null;
	
	public static function run()
	{
		Eve::startTimer('run');
		$x = new error_Handler();
		$request = new Request();
		$out = Site::route($request, $headers);
		foreach ($headers as $key => $value)
		{
			header($key . ': ' . $value);
		}
		echo $out;
		Eve::stopTimer();
	}
	
	public static function getDataDir()
	{
		return Config::get('site', 'dataDir') . '/';
	}
	
	public static function getModuleCode()
	{
		return 'site';	
	}
	
	public static function getCode()
	{
		return Eve::$siteCode;
	}
	
	public static function getDbPrefix()
	{
		return Eve::$dbConfig['prefix'] . self::$code . '_';
	}
	
	public function isModuleOn($code)
	{
		return in_array($code, static::getModules());
	}
	
	public static function lt($path = '')
	{
		$args = func_get_args();
		array_shift($args);
		return self::ltArray($path, $args);
	}
	
	public static function ltArray($path = '', $args = array())
	{
		Eve::startTimer('lt');
		$path = explode('/', $path);
		$map = self::getWebActionsMap();
		
		$pointer =& $map;
		$i = 0;
		while (($i < count($path)) && array_key_exists($path[$i], $pointer))
		{
			$pointer =& $pointer[$path[$i++]];
		}
		if (empty($pointer['_class']))
		{
			throw new Exception('Broken routing');
		}
		
		$defsCount = 0;
		$params = array();
		$extension = 'html';
		
		foreach ($pointer['_args'] as $name => $default)
		{
			$value = count($args) ? array_shift($args) : $default;
			if ($name === 'extension')
			{
				$extension = $value;
			}
			elseif ($name === 'ajax')
			{
				//$arg = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
			}
			elseif ($name === 'referer')
			{
				$params['sr'] = [self::$controller . (self::$method != 'index' ? '/' . self::$method : ''), self::$args];
			}
			elseif (strpos($name, 'get') === 0)
			{
				if (!empty($value))
				{
					$params[lcfirst(substr($name, 3))] = $value;
				}
			}
			else
			{
				if ($name === 'fullpath')
				{
					$extension = null;
				}
				//TODO null here!
				if ($default == $value)
				{
					$defsCount ++;
				}
				else
				{
					$defsCount = 0;
				}
				$path[] = $value;
			}
		}
			
		for ($i =0; $i<$defsCount; $i++)
		{
			array_pop($path);
		}
		
		$last = array_pop($path);
		if ($last != 'index')
		{
			$path[] = $last . (empty($extension) ? '' : '.' . $extension);
		}
		
		$ret = 'http://' . Config::get('site', 'domain') . '/' . implode('/', $path) . (empty($params) ? '' : '?' . http_build_query($params));
		Eve::stopTimer();
		return $ret;
	}
	
	public static function getCliActionsMap()
	{
		if (($map = cache_Array::get('cliamap')) === null)
		{
			$map = self::getActionsMap(true);
			cache_Array::set('cliamap', $map);
		}
		return $map;
	}
	
	public static function getWebActionsMap()
	{
		//echo 'X';
		if (($map = cache_Array::get('webamap')) === null)
		{
			$map = self::getActionsMap(false);
			cache_Array::set('webamap', $map);
		}
		return $map;
	}
	
	private static function getActionsMap($cli = false)
	{
		$map = array();
		foreach (Eve::getDescendants('Controller') as $className)
		{
			if ((!$cli) && is_subclass_of($className, 'controller_Tasks'))
			{
				continue;
			}
			$pointer = &$map;				
			foreach (explode('_', $className) as $key => $bit)
			{
				if (($cli || $key!=0 || lcfirst($bit) != 'frontend') && ($bit != 'Index'))
				{
					if (!array_key_exists(lcfirst($bit), $pointer))
					{
						$pointer[lcfirst($bit)] = array();
					}
					$pointer = &$pointer[lcfirst($bit)];	
				}
			}
			foreach (get_class_methods($className) as $methodName)
			{
				if (strpos($methodName, 'action') === 0)
				{
					$name = substr($methodName, strlen('action'));
					$funcPointer = &$pointer;
					if ($name != 'Index')
					{
						if (!array_key_exists(lcfirst($name), $pointer))
						{
							$pointer[lcfirst($name)] = array();
						}
						$funcPointer = &$pointer[lcfirst($name)];
					}
					
					$funcPointer['_class'] = $className;
					$funcPointer['_method'] = $methodName;
					$funcPointer['_args'] = array();
					
					$reflection = new ReflectionMethod($className, $methodName);
					foreach ($reflection->getParameters() as $param)
					{
						$funcPointer['_args'][$param->getName()] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
					}	
				}
			}
		}
		return $map;
	}
	
	public static function getUrlsMap()
	{
		return array();
	}
	
	public static function getRequest()
	{
		return static::$request;
	}
	
	public static function route(Request $request, &$headers)
	{
		static::$request = $request;
		Eve::startTimer('routing');
		if ($request->getType() == 'cli')
		{
			$map = self::getCliActionsMap();
		}
		else
		{
			$map = self::getWebActionsMap();
		}
		
		$classPath = [];
		$pointer =& $map;
		while (array_key_exists($request->glancePath(), $pointer))
		{
			$pointer =& $pointer[$request->glancePath()];
			$classPath[] = $request->shiftPath();
		}		
		
		if (empty($pointer['_class']))
		{
			Eve::stopTimer();
			throw new Exception('Not Found', 404);
		}
		$classPath = implode('/', $classPath);
		$className = $pointer['_class'];
		$methodName = $pointer['_method'];
		$methodCode = lcfirst(substr($methodName, strlen('action')));
		
		$args = array();
		$pathChecked = false;
		$extChecked = false;
		
		foreach ($pointer['_args'] as $name => $default)
		{
			if ($name == 'fullpath')
			{
				$value = implode('/', $request->getPath()) . '.' . $request->extension();
				$pathChecked = true;
				$extChecked = true;
			}
			elseif ($name == 'path')
			{
				$value = implode('/', $request->getPath());
				$pathChecked = true;
			}
			elseif (($name == 'extension') && ($request->getType() != 'cli'))
			{
				$value = $request->extension();
				$extChecked = true;
			}
			elseif ($name == 'referer')
			{
				if ($request->getParam('sr'))
				{
					$value = Site::ltArray($request->getParam('sr', 0), $request->getParam('sr', 1));
				}
				else
				{
					$value = Site::lt($default);
				}
			}
			elseif (strpos($name, 'get') === 0)
			{
				$value = $request->getParam(lcfirst(substr($name, 3)));
			}
			else
			{
				$value = $request->shiftPath();
			}
			$args[] = $value ?: $default;
		}
		
		//var_dump($request->getPath(), $pathChecked, $extChecked, $request->extension(), 'asdfasdf');
		if ($request->isPathEmpty())
		{
			$pathChecked = true;
		}
		if (!$pathChecked || (!$extChecked && ($request->extension() != 'html')))
		{
			if ($request->getType() != 'cli')
			{
				Eve::stopTimer();
				throw new Exception('Not Found', 404);
			}
		}

		self::$controller = $classPath;
		self::$method = $methodCode;
		self::$args = $args;
		
		Eve::stopTimer();
				
		Eve::startTimer('construct');
		$class = new $className($request, $methodCode);
		Eve::stopTimer();
		
		Eve::startTimer('before');
		$class->before($methodCode, $args);
		Eve::stopTimer();
		
		Eve::startTimer('action');
		$response = call_user_func_array(array($class, $methodName), $args);
		Eve::stopTimer();
		
		Eve::startTimer('after');
		$response = $class->after($response);
		Eve::stopTimer();
		
		$headers = $class->getHeaders(); 
		if (is_scalar($response) || (is_object($response) && method_exists($response, '__toString')))
		{
			return $response;
		}
		elseif(is_array($response))
		{
			return json_encode($response);
		}
		elseif(is_null($response))
		{
			throw new Exception('Not Found', 404);
		}
		else
		{
			throw new Exception('Unknown object returned from action');
		}
	}
	
	public static function redirectTo($url)
	{
		header('Location: ' . $url);
		exit;
	}
}
