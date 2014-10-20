<?php
/**
 * Action.
 * 
 * @author fsw
 */

abstract class Action_Http extends Action
{	
        protected function post($post){
	    //TODO 404 here?
        }

	public function run()
	{
	    if(!empty($_POST)){ $this->post($_POST); }
                //die('override me');
	}
	
	protected function redirectTo($url)
	{
		header('Location: ' . $url);
		exit;
	}
	
	
	public static function lt($args = [])
	{
	  $args = func_get_args();
	  
	  $name = lcfirst(str_replace('Action_', '', get_called_class()));
	  
	  foreach(Eve::getClassAnnotations(get_called_class()) as $annotation){
		if ($annotation instanceof UrlName){ 
                    $name = $annotation->value;
		}
	  }
	  
	  return '/' . $name . (empty($args) ? '' : ('/' . implode('/', $args)));
	}
	
}
