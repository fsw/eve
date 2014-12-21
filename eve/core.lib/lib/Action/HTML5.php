<?php
/**
 * Action.
 * 
 * @author fsw
 */

//TODO separate this bits

Eve::requireVendor('twig/lib/Twig/Autoloader.php');
Twig_Autoloader::register();

class Action_HTML5_TwigLoader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface {
    
    public function getSource($name) {
        $templateFile = str_replace('.php', '.twig', Eve::getClassFileName($name));
        $contents = (($name === 'Action_HTML5') ? '' : ('{% extends "' . get_parent_class($name) . '" %}'));
        if (file_exists($templateFile)) {
            $contents .=  file_get_contents($templateFile);
        }
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
    
}

abstract class Action_HTML5 extends Action_Http implements ArrayAccess
{	
    //array access
    public function offsetExists ($offset) {
        return true;
    }
    
    public function offsetGet ($offset) {
        if (property_exists($this, $offset)) {
            return $this->$offset;
        } elseif (method_exists($this, 'get' . ucfirst($offset))) {
            return call_user_func([$this, 'get' . ucfirst($offset)]);
        } else {
            throw new Exception('unknow action field accessed from template ' . $offset);
        }
    }
    
    public function offsetSet ($offset, $value) {
        throw new Exception('Action have a read-only ArrayAccess');
    }
    
    public function offsetUnset ($offset) {
        throw new Exception('Action have a read-only ArrayAccess');   
    }
    
	protected function getStylesheetsUrls(){
	      return ['/static/css/bootstrap.min.css', '/static/css/bootstrap-theme.min.css'];
	}
	
	protected function getScriptsUrls() {
	      return ['/static/js/vendor/bootstrap.min.js', '/static/js/main.js'];
	}
	
	protected function getSeoTitle() {
	      return "Eve";
	}
	
	protected function getSeoDescription() {
	      return "this site was built with Eve Framework";
	}
	
	protected function getSeoKeywords(){
	      return "";
	}
	
	public function run(){ 
	  parent::run();
	  //TODO cache in production mode 
	  $twig = new Twig_Environment(new Action_HTML5_TwigLoader(), ['cache' => false]); //'cache/twig']);
	  //TODO how to hack into
	  //template.getAttribute($object, $item, array $arguments = array(), $type = Twig_Template::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
	  echo $twig->loadTemplate(get_class($this))->render(['this' => $this]);
	}
	
}
