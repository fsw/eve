<?php

class Action_AdminEntityCRUD extends Action_Admin {
  
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
      
      if(isset($post['save']) && ($post['save'] == 'save')){
	try {
	    $newId = $this->entity->save();
	    $this->redirectTo(call_user_func(array($this->listAction, 'lt')) . '#e' . $newId);
	} catch(Entity_Exception $e){
	    $this->error = $e;
	}
      }
      
      if($this->entity->id && isset($post['delete']) && ($post['delete'] == 'delete')){
	  if(isset($post['confirm']) && ($post['confirm'] == 'confirm')){
	      $this->entity->delete();
	      $this->redirectTo(call_user_func(array($this->listAction, 'lt')));
	  }
	  $this->delete_form = true;
      }

  }

  protected function sectionContent(){ ?>
      <h1><a href="<?php echo call_user_func(array($this->listAction, 'lt')); ?>"><?php echo call_user_func(array(static::$entityClass, 'getPlural')); ?></a> &gt;&gt; <?php echo static::$entityClass ?> #<?php echo $this->id ?></h1>
      <form class="form-horizontal" action="" enctype="multipart/form-data" method="post" >
	<?php if ($this->delete_form): ?>
	    <fieldset>
	      <legend>Delete Entity</legend>
	      <div class="form-group">
		<div class="col-md-12">
		  You are about to delete "<?php echo $this->entity ?>". <br/>
		  This is <strong>not reversible</strong> operation. <br/>
		  Please check the box and click Delete again to confirm.
		</div>
	      </div>
	      <div class="form-group">
		<div class="col-md-3">&nbsp;</div>
		<div class="col-md-9 clearfix">
		  <label for="confirm">
		    <input type="checkbox" name="confirm" id="confirm" value="confirm"> I am sure I want to delete this item.
		  </label>
		</div>
	      </div>
	      <div class="form-group">
		<label class="col-md-3 control-label" for="save"></label>
		<div class="col-md-9 clearfix">
		  <button id="delete" name="delete" class="pull-right btn btn-danger" value="delete"><i class="fa fa-trash"></i> Delete</button>
		</div>
	      </div>
	    </fieldset>
	<?php else:; ?>
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
	  <div class="col-md-9 clearfix">
	    <?php if($this->entity->id): ?>
	      <button id="save" name="save" value="save" class="pull-right btn btn-primary" style="margin-left:10px;"><i class="fa fa-check"></i> Save</button>
	    <?php else: ?>
	      <button id="save" name="save" value="save" class="pull-right btn btn-success" style="margin-left:10px;"><i class="fa fa-plus"></i> Add</button>
	    <?php endif; ?>
	    <a id="cancel" href="<?php echo call_user_func(array($this->listAction, 'lt')); ?>" class="pull-right btn btn-warning" style="margin-left:90px;"><i class="fa fa-close"></i> Cancel</a>
	    <?php if($this->entity->id): ?>
	      <button id="delete" name="delete" class="pull-right btn btn-danger" value="delete"><i class="fa fa-trash"></i> Delete</button>
	    <?php endif; ?>
	  </div>
	</div>

	</fieldset>
	<?php endif; ?>
      </form>
   <?php }
}
