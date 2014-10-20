<?php

abstract class Action_EntityForm extends Action_Form {

    protected static $entityClass = 'None';
    
    public function run(){
	$this->success = (!empty($_GET['success'])) && ($_GET['success'] == 'true');
	//$this->success = (!empty($_GET['success'])) && ($_GET['success'] == 'true');
		
	if($this->id != 0) {
	  $this->entity = call_user_func(array(static::$entityClass, 'getById'), $this->id);
	} else {
	  $this->entity = new static::$entityClass();
	}
	parent::run();
    }

    protected static function getFields(){
        $method = new ReflectionMethod(static::$entityClass, 'getFields');
	$method->setAccessible(true);
	return $method->invoke(null);
    }

    protected function success(){
	  $this->redirectTo('?success=true&id=' . $newId);
    }
    
    protected function post($post){
      
      foreach($this->fields as $key=>$field){
	  $this->entity->$key = $field->updateWithPost($this->entity->$key, $post);
      }
      
      try {
	  $newId = $this->entity->save();
      } catch(Entity_Exception $e) {
	  $this->error = $e;
      }
      
      if(empty($this->error)) {
	  $this->success();
      }
  }

    
}
