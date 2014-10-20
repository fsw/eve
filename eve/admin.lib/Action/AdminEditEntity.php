<?php

class Action_AdminEditEntity extends Action_Admin {
  
  public static $entityClass = '';
  protected $listAction = '';
  
  /** @Param('int') */
  public $id;
  
  protected $fields;
  protected $entity;
  protected $error;
  
  public function run(){
      foreach(Eve::getDescendants('Action_AdminListEntities') as $listAction){
	  //TODO select class lowest in hierarchy if possible
	  $listActionClass = (new ReflectionProperty($listAction, 'entityClass'))->getValue();
	  if($listActionClass == static::$entityClass){
	      $this->listAction = $listAction;
	      break;
	  }
      }
      $method = new ReflectionMethod(static::$entityClass, 'getFields');
      $method->setAccessible(true);
      $this->fields = $method->invoke(null);
      
      if($this->id != 0) {
	$this->entity = call_user_func(array(static::$entityClass, 'getById'), $this->id);
      } else {
        $this->entity = new static::$entityClass();
      }
      
      parent::run();
  }
  
  protected function post($post){
      
      foreach($this->fields as $key=>$field){
	  $this->entity->$key = $field->updateWithPost($this->entity->$key, $post);
      }
      
      try {
	  $newId = $this->entity->save();
      } catch(Entity_Exception $e){
	  $this->error = $e;
      }
      if(empty($this->error)) {
	  $this->redirectTo(call_user_func(array($this->listAction, 'lt')) . '#e' . $newId);
      }
  }

  protected function sectionContent(){ ?>
      <h1><a href="<?php echo call_user_func(array($this->listAction, 'lt')); ?>"><?php echo call_user_func(array(static::$entityClass, 'getPlural')); ?></a> &gt;&gt; <?php echo static::$entityClass ?> #<?php echo $this->id ?></h1>
      <form class="form-horizontal" action="" enctype="multipart/form-data" method="post" >
	<fieldset>

	<!-- Form Name -->
	<legend>Edit Entity</legend>
	<?php if(!empty($this->error)): ?>
	    <div class="form-group alert alert-danger" role="alert">
		<?php echo $this->error->getMessage(); ?>
	    </div>
	<?php endif; ?>

	<?php foreach($this->fields as $key=>$field): ?>
	  <?php if($field->hasFormInput()): ?>
	  <div class="form-group">
	    <label class="col-md-3 control-label" for="textinput"><?php echo $field->getVerboseName(); ?></label>  
	    <div class="col-md-9">
	    <?php echo $field->getFormInput($this->entity->$key) ?>
	    <span class="help-block"><?php echo $field->getHelpText(); ?></span>
	    </div>
	  </div>
	  <?php endif; ?>
	<?php endforeach; ?>

	<!-- Button (Double) -->
	<div class="form-group">
	  <label class="col-md-3 control-label" for="save"></label>
	  <div class="col-md-9">
	    <button id="save" name="save" class="btn btn-primary">Save</button>
	    <a id="cancel" href="<?php echo call_user_func(array($this->listAction, 'lt')); ?>" class="btn btn-danger">Cancel</a>
	  </div>
	</div>

	</fieldset>
      </form>
   <?php }
}
