<?php
/**
 * Task.
 * 
 * @package Framework
 * @author fsw
 */

class Task
{
	
	private $code;
	
	public function __construct($code)
	{
		$this->code = $code;
	}
	
	public function run($args)
	{		
		$path = Eve::find('tasks/' . $this->code . '.php');
		if ($path)
		{
			include($path);
		}
	}

}
