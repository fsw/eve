<?php
/**
 * Action.
 * 
 * @author fsw
 */

abstract class Action_HTML5 extends Action_Http
{	
	protected function getStylesheetsUrls(){
	      return ['/static/css/bootstrap.min.css', '/static/css/bootstrap-theme.min.css'];
	}
	
	protected function getScriptsUrls(){
	      return ['/static/js/vendor/bootstrap.min.js', '/static/js/main.js'];
	}
	
	protected function getSeoTitle(){
	      return "";
	}
	
	protected function getSeoDescription(){
	      return "";
	}
	
	protected function getSeoKeywords(){
	      return "";
	}
	
	public function run(){ 
	  parent::run();
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
		    <link rel="stylesheet" href="<?php echo $url; ?>?<?php echo EVE_BUILD_ID ?>">
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
		    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		    <script>window.jQuery || document.write('<script src="/static/js/vendor/jquery-1.11.1.min.js"><\/script>')</script>

		    <?php foreach($this->getScriptsUrls() as $url) :?>
		      <script src="<?php echo $url; ?>?<?php echo EVE_BUILD_ID ?>"></script>
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
