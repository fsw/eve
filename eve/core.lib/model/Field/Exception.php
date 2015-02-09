<?php

class Field_Exception extends Exception
{

    public $key;

    public $message;

    public function __construct($key, $message)
    {
        $this->key = $key;
        $this->message = $message;
    }
}