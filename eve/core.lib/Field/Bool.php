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
		return isset($_POST[$this->name]) ? $post[$this->name] == "on" : $value;
        }
        
	public function toDbRow($value){
		return [$this->name => $value ? 1 : 0];
        }

	public function getFormInput($value) {
		return '<input id="' . $this->name . '" name="' . $this->name . '" value="on" type="checkbox" ' . ($value ? ' checked="checked"' : '') . '" '. ( $this->isRequired() ? ' required=""' : '').'>';
	}
	
}
/*
{	
	private $default;
	
	public function __construct($default = false)
	{
		$this->default = $default;
	}
	
	public function getDbDefinition()
	{
		return 'tinyint(1) NOT NULL' . ($this->default ? ' DEFAULT 1' : '');
	}
	
	public function getFormInput($key, $value)
	{
		if ($value===null && $this->default)
		{
			$value = 1;
		}
		return '<input type="hidden" name="' . $key . '" value="0"><input type="checkbox" name="' . $key . '" value="1"' . (!empty($value) ? ' checked="checked"' : '') . '>';
	}
	
	public function toDb($value, $key, $code, &$row)
	{
		return empty($value) ? 0 : 1;
	}
	
	public function getLoremIpsum()
	{
		return rand(0, 1);
	}
}
