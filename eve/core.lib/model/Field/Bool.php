<?php

class Field_Bool extends Field {

        public function getDbDefinition()
	{
		return [$this->name => 'tinyint(1) NOT NULL'];
	}
	
	public function getDefault() {
		return false;
        }
        
	public function fromDbRow($row){
		return (bool)!empty($row[$this->name]);
        }
	
	public function updateWithPost($value, $post){
		return isset($_POST[$this->name]) ? ($post[$this->name] == "on") : false;
        }
        
	public function toDbRow($value){
		return [$this->name => $value ? 1 : 0];
        }

	public function getFormInput($value) {
		return '<input id="' . $this->name . '" name="' . $this->name . '" value="on" type="checkbox" ' . ($value ? ' checked="checked"' : '') . '" '. ( $this->isRequired() ? ' required=""' : '').'>';
	}
	
}
