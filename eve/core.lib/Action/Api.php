<?php
/**
 * Action.
 * 
 * @author fsw
 */

class Action_Api extends Action_Http
{

	/** @Param('string') */
	public $entity;
	/** @Param('string') */
	public $method;
	
	public function run()
	{
	      header('Content-type: application/json');
	      $ret = null;
	      //TODO call this automagicially based on privilages
	      if (($this->entity == 'CommonImage') && ($this->method == 'getAll'))
		  $ret = CommonImage::getAll();
	      echo json_encode($ret);
	}
}
