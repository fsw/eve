<?php

class Field_DateTime extends Field
{

    public $default;

    public function getDefault()
    {
        return strtotime($this->default);
    }

    public function updateWithPost($value, $post)
    {
        return isset($post[$this->name]) ? strtotime($post[$this->name]) : $value;
    }

    public function fromDbRow($row, $db)
    {
        return strtotime($row[$this->name]);
    }

    public function toDbRow($value)
    {
        return [$this->name => date('Y-m-d H:i:s', $value)];
    }

    public function getFormInput($value)
    {
        return '<input id="' . $this->name . '" name="' . $this->name . '" type="text" value="' .
                 date('Y-m-d H:i:s', $value) . '" placeholder="" class="form-control input-md"' .
                 ($this->isRequired() ? ' required=""' : '') . '>';
    }

    public function validate($value)
    {
        return parent::validate($value);
    }

    public function getDbDefinition()
    {
        return [$this->name => 'timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\''];
    }
}
