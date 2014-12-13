<?php

class Field_Email extends Field_String
{

    public function validate ($value) {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return [sprintf('%s is not a valid email address', $value)];
        }
        return [];
    }

    public function updateWithPost ($value, $post) {
        return isset($_POST['not' . $this->name]) ? $post['not' . $this->name] : $value;
    }

    public function getFormInput ($value) {
        return '<input id="not' . $this->name . '" name="not' . $this->name . '" type="text" value="' . $value .
        '" placeholder="" class="form-control input-md"' . ($this->isRequired() ? ' required=""' : '') . '>';
    }
    
    /* public static function obfuscate ($val) {
        return substr($val, 0, 1) . '...' . substr($val, strpos($val, '@'));
    }*/
}
