<?php

abstract class Action_Form extends Action_Frontend {
   
   public function run(){
      $this->fields = static::getFields();
      parent::run();
  }
  protected function getScriptsUrls(){
	  $ret = parent::getScriptsUrls();
	  $ret[] = '/static/js/fields.js';
          return $ret;
  }
    
    protected static function getFields(){
	//var_dump("ASDASDXXXX", get_called_class());
        $ret = array();
        foreach(Eve::getFieldsAnnotations(get_called_class()) as $field => $annotations){ 
	    //var_dump("33333");
            foreach($annotations as $annotation){
		//var_dump($annotation);
                if ($annotation instanceof Field){ 
		    //var_dump($annotation);
                    $annotation->name = $field;
                    $annotation->entity = get_called_class();
                    $ret[$field] = $annotation;
                }
            }
        }
        return $ret;
    }
    
    protected function renderField($key){
      if($this->fields[$key]->hasFormInput()): ?>
	  <div class="form-group<?php if($this->fields[$key]->isRequired()) echo ' required'; ?>">
	    <label class="col-md-3 control-label" for="textinput"><?php echo $this->fields[$key]->getVerboseName(); ?></label>  
	    <div class="col-md-9">
	    <?php echo $this->fields[$key]->getFormInput($this->$key) ?>
	    <span class="help-block"><?php echo $this->fields[$key]->getHelpText(); ?></span>
	    </div>
	  </div>
      <?php endif;
    }

   protected function post($post){
      
      foreach($this->fields as $key=>$field){
	  $this->$key = $field->updateWithPost($this->$key, $post);
      }
      
      try {
	  $newId = $this->entity->save();
      } catch(Entity_Exception $e){
	  $this->error = $e;
      }
      
      if(empty($this->error)) {
	  $this->redirectTo('?success');
      }
  }
  
   protected function sectionContent(){
   
   }
        	
}
