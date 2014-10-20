<?php

class Entity_Promise {

      private $_promisedEntity;
      public $id;
      private $_entity;
      
      
      function __construct($_promisedEntity, $id) {
	  $this->_promisedEntity = $_promisedEntity;
	  $this->id = $id;
	  $this->_entity = null;
      }
      
      public function __call($name, $arguments) {
	  if($this->_entity === null){
		  $this->_entity = call_user_func(array($this->_promisedEntity, 'getById'), $this->id);
	  }
	  return call_user_func_array(array($this->_entity, $name), $arguments);
      }

      public static function __callStatic($name, $arguments) {
	  if($this->_entity === null){
		  $this->_entity = call_user_func(array($this->_promisedEntity, 'getById'), $this->id);
	  }
	  return call_user_func_array(array($this->_entity, $name), $arguments);
      }
      
      public function __set($name, $value) {
	  if($this->_entity === null){
		  $this->_entity = call_user_func(array($this->_promisedEntity, 'getById'), $this->id);
	  }
	  $this->_entity->$name = $value;
      }

      public function __get($name) {
	  if($this->_entity === null){
		  $this->_entity = call_user_func(array($this->_promisedEntity, 'getById'), $this->id);
	  }
	  return $this->_entity->$name;
      }
      
      public function __toString(){
	  if(empty($this->id)) {
	      return "None";
	  }
	  if($this->_entity === null){
	      $this->_entity = call_user_func(array($this->_promisedEntity, 'getById'), $this->id);
	  }
	  return $this->_entity->__toString();
      }
}