<?php

class Action_AdminJson extends Action_Json {
   
   public function run(){
      if (empty($_SESSION['admin'])){
	throw new Exception('TODO ACCESS EXCEPTION');
      } else {
        $this->admin = unserialize($_SESSION['admin']);
      }
      parent::run();
   }
   
}
