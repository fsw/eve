<?php

abstract class Action_AdminListEntities extends Action_Admin {

   public static $entityClass = '';
   public static $previewAction = '';
   
   protected $editAction = '';
   
   public function run(){
      foreach(Eve::getDescendants('Action_AdminEditEntity') as $editAction){
	  //TODO select class lowest in hierarchy if possible
	  $editActionClass = (new ReflectionProperty($editAction, 'entityClass'))->getValue();
	  if($editActionClass == static::$entityClass){
	      $this->editAction = $editAction;
	      break;
	  }
      }
      $method = new ReflectionMethod(static::$entityClass, 'getFields');
      $method->setAccessible(true);
      $this->fields = $method->invoke(null);
      $this->columns = call_user_func(array(static::$entityClass, 'getAdminColumns'));
      parent::run();
   }
   
   protected static function moduleDescription(){ ?>
	<div class="col-md-4">  
          <h2><?php echo call_user_func(array(static::$entityClass, 'getPlural')) ?></h2>
          <p>In this module, you can view and edit <?php echo call_user_func(array(static::$entityClass, 'getPlural')) ?></p>
          <p>
	      <a class="btn btn-default" href="<?php echo static::lt() ?>" role="button">View <?php echo call_user_func(array(static::$entityClass, 'getPlural')) ?> &raquo;</a>
	      
          </p>
        </div>
   <?php }
   
   protected function sectionContent(){ ?>
      		  
      <h1>
	<?php echo call_user_func(array(static::$entityClass, 'getPlural')) ?>
	<a class="btn btn-lg btn-success pull-right" href="<?php echo call_user_func(array($this->editAction, 'lt'), 0); ?>">
	  <span class="glyphicon glyphicon-plus"></span>
	  Add <?php echo static::$entityClass ?>
	</a>
      </h1>
      <table class="table table-hover">
	<thead>
	  <tr>
	    <th>#</th>
	    <?php foreach( $this->columns as $key): ?>
	      <th><?php echo isset($this->fields[$key]) ? $this->fields[$key]->getVerboseName() : str_replace('get', '', $key); ?></th>
	    <?php endforeach; ?>
	    <th></th>
	  </tr>
	</thead>
	<tbody>
        <?php foreach(call_user_func(array(static::$entityClass, 'getAll')) as $entity): ?>
	  <tr id="e<?php echo $entity->id ?>">
	      <td class="text-muted"><?php echo $entity->id ?></td>
	      <?php foreach( $this->columns as $key): ?>
		<td>
		      <?php if (($key == 'name') && (property_exists($entity, 'level'))): ?>
		      <?php echo str_repeat('&nbsp;&nbsp;&nbsp;', $entity->level); ?>
		      <span class="glyphicon glyphicon-chevron-right"></span>
		      <?php endif; ?>
		
		      <?php if(isset($this->fields[$key])): ?>
			  <?php if(is_a($this->fields[$key], 'Field_Bool')): ?>
			      <?php echo $entity->$key ? '<span class="text-success glyphicon glyphicon-ok"></span>' : '<span class="text-muted glyphicon glyphicon-remove"></span>';?>
			  <?php else: ?>
			      <?php echo $entity->$key; ?>
			  <?php endif; ?>
		      <?php else: ?>
		      <?php echo property_exists($entity, $key) ? $entity->$key : $entity->$key(); ?>
		      <?php endif; ?>
		</td>
	      <?php endforeach; ?>
	      <td>
	      <div class="pull-right">
		  <a class="btn btn-warning" href="<?php echo call_user_func(array($this->editAction, 'lt'), $entity->id); ?>">
		    <span class="glyphicon glyphicon-edit"></span>
		    Edit
		  </a>&nbsp;<?php 
		    if (method_exists($entity, 'getUrl')): 
		  ?><a class="btn btn-default" target="_blank" href="<?php echo $entity->getUrl(); ?>">
		    <span class="glyphicon glyphicon-share"></span>
		    Preview
		  </a>
		  <?php endif; ?>
	      </div>
	      </td>
	  </tr>
        <?php endforeach; ?>
	</tbody>
      </table>
        
   <?php }
}
