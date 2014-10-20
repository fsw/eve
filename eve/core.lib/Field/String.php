<?php

class Field_String extends Field
{
	public $maxLength;
	public $minLength;
	public $suggestedChoices;

	public function getFormInput($value) {
		$ret = "";
		if (!empty($this->suggestedChoices)) {
		      $ret .= '<select name="' . $this->name . '_sugg" class="form-control input-md suggestions">';
		      $ret .= '<option value=""> -- please select -- </option>';
		      foreach ($this->suggestedChoices as $choice) {
			    $ret .= '<option value="' . $choice . '">' . $choice . '</option>';
		      }
		      $ret .= '<option value="_other">other...</option>';
		      $ret .= "</select>";
		}
		return $ret . '<input id="' . $this->name . '" name="' . $this->name . '" type="text" value="' . $value . '" placeholder="" class="form-control input-md"'. ( $this->isRequired() ? ' required=""' : '').'>';
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
}
