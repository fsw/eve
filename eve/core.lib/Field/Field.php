<?php
/**
 * Value.
 * 
 * @package Core
 * @author fsw
 */

abstract class Field extends Annotation {
        
        public $name;
        public $entity;
        
        public $verboseName;
        public $helpText;
        public $hasFormInput;
        
        
        public $required;
        
        
        public function getVerboseName() {
		return empty($this->verboseName) ? ucfirst(str_replace('_', ' ', $this->name)) : $this->verboseName;
        }
        
        public function getHelpText() {
		return empty($this->helpText) ? "" : $this->helpText;
        }
        
        public function getDefault() {
		return null;
        }
        

        public function isRequired() {
		return !empty($this->required);
        }
        
        public function updateWithPost($value, $post){
		return isset($_POST[$this->name]) ? $post[$this->name] : $value;
        }
        
        public function fromDbRow($row){
		return $row[$this->name];
        }
        
        public function toDbRow($value){
		return [$this->name => $value];
        }
        
        public function hasFormInput() {
        	return $this->hasFormInput === null ? true : $this->hasFormInput;
        }
        
        public function getFormInput($value){
		return '<input id="' . $this->name . '" name="' . $this->name . '" type="text" value="' . $value . '" placeholder="" class="form-control input-md"'. ( $this->isRequired() ? ' required=""' : '').'>';
        }
        
        public function getDbDefinition()
	{
		return [$this->name => 'varchar(255) DEFAULT NULL'];
	}
	
	public function validate($value)
	{
		return true;
	}
	
	public function getLoremIpsum()
	{
		return rand(0, 100);
	}
	
}
