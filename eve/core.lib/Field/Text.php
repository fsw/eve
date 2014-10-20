<?php

class Field_Text extends Field
{
        public function getFormInput($value){
		return '<textarea id="' . $this->name . '" name="' . $this->name . '" class="form-control input-md"'. ( $this->isRequired() ? ' required=""' : '').'>' . $value . '</textarea>';
        }
        
        public function getDbDefinition()
	{
		return [$this->name => 'text'];
	}
}

/*
	public function __construct($params = array())
	{
		$this->minLength = empty($params['minLength']) ? 0 : $params['minLength'];
		$this->maxLength = empty($params['maxLength']) ? 255 : $params['maxLength'];
		$this->placeholder = empty($params['placeholder']) ? '' : $params['placeholder'];
	}
	
	public function getLoremIpsum()
	{
		$ret = '';
		$template = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ          ';
		$len = rand($this->minLength, min($this->maxLength, 50));
		for ($a = 0; $a <= $len; $a++)
		{
			$ret .= $template[rand(0, strlen($template) - 1)];
		}
		return $ret;
	}
	
	public function getDbDefinition()
	{
		return 'varchar(' . $this->maxLength . ') ' . 'NOT NULL';
	}

	public function validate($data)
	{
		return true;
	}

	public function getJsRegexp()
	{
		return '';
	}
	
	public function getFormInput($key, $value)
	{
		return '<input type="text" name="' . $key . '" value="' . $value . '" placeholder="' . $this->placeholder . '" />';
	}
*/
