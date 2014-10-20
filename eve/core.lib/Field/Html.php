<?php

class Field_Html extends Field_Text
{
        public function getFormInput($value){
		return '<textarea id="' . $this->name . '" name="' . $this->name . '" style="height: 400px;" class="wysiwygEditor content form-control input-md"'. ( $this->isRequired() ? ' required=""' : '').'>' . $value . '</textarea>';
        }
        
}