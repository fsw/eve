<?php

class Field_Float extends Field
{
	public $min;
	public $max;
	
	public function getDbDefinition()
	{
		return [$this->name => 'float NOT NULL'];
	}
	
	public function getDefault() {
		return 0;
        }
        
        public function getFormInput($value){
		return '<input id="' . $this->name . '" name="' . $this->name . '" type="number" step="any" value="' . $value . '" placeholder="" class="form-control input-md"'. ( $this->isRequired() ? ' required=""' : '').'>';
        }
}
