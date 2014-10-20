<?php

class Field_Int extends Field
{
	public $min;
	public $max;
	
	public function getDbDefinition()
	{
		return [$this->name => 'int(11) NOT NULL'];
	}
	
	public function getDefault() {
		return 0;
        }

}
