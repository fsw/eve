<?php
/**
 * Action.
 * 
 * @author fsw
 */

Eve::requireVendor('twig/lib/Twig/Autoloader.php');
Twig_Autoloader::register();

abstract class Action_HTML5 extends Action_Http implements Twig_LoaderInterface, Twig_ExistsLoaderInterface
{	
    public function getSource($name) {
        $templateFile = str_replace('.php', '.twig', Eve::getClassFileName($name));
        $contents = (($name === 'Action_HTML5') ? '' : ('{% extends "' . get_parent_class($name) . '" %}'));
        if (file_exists($templateFile)) {
            $contents .=  file_get_contents($templateFile);
        }
        //var_dump($name);
        //var_dump(get_parent_class($name));
        //var_dump($contents);
        return $contents; 
    }
    
    public function exists($name) {
        return file_exists(str_replace('.php', '.twig', Eve::getClassFileName($name)));
    }
    
    public function getCacheKey($name) {
        return $name;
    }
    
    public function isFresh($name, $time) {
        //TODO
        return false;
    }
    
	protected function getStylesheetsUrls(){
	      return ['/static/css/bootstrap.min.css', '/static/css/bootstrap-theme.min.css'];
	}
	
	protected function getScriptsUrls() {
	      return ['/static/js/vendor/bootstrap.min.js', '/static/js/main.js'];
	}
	
	protected function getSeoTitle() {
	      return "";
	}
	
	protected function getSeoDescription() {
	      return "";
	}
	
	protected function getSeoKeywords(){
	      return "";
	}
	
	public function run(){ 
	  parent::run(); 
	  //$loader = new Twig_Loader_Filesystem('/path/to/templates');
	  $twig = new Twig_Environment($this, ['cache' => false]);
	  
	  $template = $twig->loadTemplate(get_class($this));
	  echo $template->render((array)$this);
	  exit;
	  ?><!DOCTYPE html>
	    <html class="no-js">
		<head>
		    <meta charset="utf-8">
		    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		    <title><?php echo $this->getSeoTitle(); ?></title>
		    <meta name="description" content="<?php echo $this->getSeoDescription(); ?>">
		    <meta name="keywords" content="<?php echo $this->getSeoKeywords(); ?>">
		    <meta name="viewport" content="width=device-width, initial-scale=1">
		    <?php foreach($this->getStylesheetsUrls() as $url) :?>
		    <link rel="stylesheet" href="<?php echo $url; ?>">
		    <?php endforeach; ?>

		    <!--[if lt IE 9]>
			<script src="/static/js/vendor/html5-3.6-respond-1.1.0.min.js"></script>
		    <![endif]-->
		    <?php $this->sectionExtraHead(); ?>
		</head>
		<body>
		    <!--[if lt IE 7]>
			<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
		    <![endif]-->
		    <?php $this->sectionBody(); ?>
		    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		    <script>window.jQuery || document.write('<script src="/static/js/vendor/jquery-1.11.1.min.js"><\/script>')</script>

		    <?php foreach($this->getScriptsUrls() as $url) :?>
		      <script src="<?php echo $url; ?>"></script>
		    <?php endforeach; ?>
		    
		    <?php $this->sectionExtraBody(); ?>
		</body>
	    </html>
	<?php }
	
	protected function sectionBody()
	{
	    
	    echo "OVERRIDE ME MON";
	}
	
	protected function sectionExtraBody() {}
	protected function sectionExtraHead() {}
}
