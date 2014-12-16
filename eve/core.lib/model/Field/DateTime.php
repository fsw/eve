<?php

class Field_DateTime extends Field
{
    public $default;
    
    public function validate ($value) {
        return parent::validate($value);
    }

    public function getDbDefinition () {
        return [
                $this->name => 'timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\''
        ];
    }
}
