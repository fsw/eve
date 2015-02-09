<?php

class Field_Honeypot extends Field_String
{

    public function validate($value)
    {
        if (! empty($value)) {
            return ['you cant be a robot'];
        }
        return [];
    }

    public function updateWithPost($value, $post)
    {
        return isset($post['email']) ? $post['email'] : $value;
    }

    public function getFormInput($value)
    {
        return '<input id="email" name="email" type="text" value="' . $value .
                 '" placeholder="" class="form-control input-md">';
    }
}
