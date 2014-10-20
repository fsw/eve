<?php

class Action_Admin extends Action_HTML5 {
   
   public function run(){
      if ((get_class($this) != 'Action_AdminLogin') && empty($_SESSION['admin'])){
	$this->redirectTo(Action_AdminLogin::lt());
      } else {
        $this->admin = unserialize($_SESSION['admin']);
      }
      //var_dump($this->admin);
      parent::run();
   }
   
   protected static function moduleDescription(){ 
      return null;
   }
   
   
   
   protected function getStylesheetsUrls(){
	  $ret = parent::getStylesheetsUrls();
	  $ret[] = '/static/css/bootstrap3-wysihtml5.min.css';
	  $ret[] = 'http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css';
	  $ret[] = '/static/css/admin.css';
          return $ret;
   }

   protected function getScriptsUrls(){
	  $ret = parent::getScriptsUrls();
	  $ret[] = '/static/js/vendor/bootstrap3-wysihtml5.all.min.js';
	  $ret[] = '/static/js/admin.js';
          return $ret;
   }
	
   protected function sectionBody(){ ?>
      <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo Action_Admin::lt(); ?>">Admin Panel</a>
        </div>
        <div class="navbar-collapse collapse">
          <?php if(get_class($this) != 'Action_AdminLogin'): ?>
          <ul class="nav navbar-nav">
            <!--<li class="active"><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>-->
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Website Data <span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">
                <?php foreach(Eve::getDescendants('Action_AdminListEntities') as $listAction): ?>
                <li><a href="<?php echo $listAction::lt(); ?>"><?php echo str_replace('List', '', str_replace('Action_Admin', '', $listAction)); ?></a></li>
                <?php endforeach; ?>
              </ul>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <span class="glyphicon glyphicon-user"></span>
              <?php echo $this->admin->email; ?>
              <span class="caret"></span>
              </a>
              <ul class="dropdown-menu" role="menu">
                <li>
                <form class="navbar-form" role="form" action="<?php echo Action_AdminLogin::lt() ?>" method="post">
		  <input type="hidden" name="action" value="logout"/>
		  <button type="submit" class="btn btn-danger">
		    <span class="glyphicon glyphicon-off"></span>
		    Log out
		  </button>
		</form>
                </li>
              </ul>
            </li>
          </ul>
          <?php endif; ?>
        </div><!--/.navbar-collapse -->
      </div>
    </div>

    <!-- <div class="jumbotron">
      <div class="container">
        <h1>Hello, world!</h1>
        <p>This is a template for a simple marketing or informational website. It includes a large callout called a jumbotron and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
        <p><a class="btn btn-primary btn-lg" role="button">Learn more &raquo;</a></p>
      </div>
    </div>-->

    <div class="container">
      <?php $this->sectionContent(); ?>
      <footer class="text-muted">
        <p>&copy; 2014</p>
      </footer>
    </div> <!-- /container -->
   <?php }
   
   protected function sectionContent(){ ?>
        
      <h1>Admin Panel Dashboard</h1>
      <div class="row">
        <?php foreach(Eve::getDescendants('Action_Admin') as $action): ?>
        <?php echo call_user_func(array($action, 'moduleDescription')); ?>
        <?php endforeach; ?>
      </div>

      <hr>
   <?php }
        	
}
