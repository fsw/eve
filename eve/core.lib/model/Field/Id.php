<?php

class Field_Id extends Field {


	public function updateWithPost($value, $post) {
		return $value;
	}
	
	public function toDbRow($value){
		return [];
        }

        public function hasFormInput() {
		return false;
        }
        /*
	public function getFormInput($value) {
		return '<div class="form-control"><input type="hidden" name="' . $this->name . '" value="' . $value . '" />#' . $value . '</div>';
	}*/
	
        public function getDbDefinition()
	{
		return [$this->name => 'int(11) NOT NULL AUTO_INCREMENT'];
	}
}
