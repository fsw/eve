<?php

class Field_String extends Field
{

    public $maxLength;

    public $minLength;

    public $suggestedChoices;

    public function getFormInput($value)
    {
        $ret = "";
        if (! empty($this->suggestedChoices)) {
            $ret .= '<select name="' . $this->name . '_sugg" class="form-control input-md suggestions"' .
                     ($this->isRequired() ? ' required=""' : '') . '>';
            $ret .= '<option value=""> -- please select -- </option>';
            $inSuggestions = false;
            foreach ($this->suggestedChoices as $choice) {
                $selected = $value == $choice;
                $ret .= '<option value="' . $choice . '"' . ($selected ? ' selected="selected"' : '') . '>' . $choice .
                         '</option>';
                if ($selected) {
                    $inSuggestions = true;
                }
            }
            $ret .= '<option value="_other"' . ($value && ! $inSuggestions ? ' selected="selected"' : '') .
                     '>other...</option>';
            $ret .= "</select>";
        }
        return $ret . '<input id="' . $this->name . '" name="' . $this->name . '" type="text" value="' . $value .
                 '" placeholder="" class="form-control input-md"' . ($this->isRequired() ? ' required=""' : '') . '>';
    }

    public function validate($value)
    {
        $errors = parent::validate($value);
        if ($this->minLength && strlen($value) < $this->minLength) {
            $errors[] = sprintf('minimum %d characters', $this->minLength);
        }
        if ($this->maxLength && strlen($value) > $this->maxLength) {
            $errors[] = sprintf('maximum %d characters', $this->maxLength);
        }
        return $errors;
    }
    /*
     *
     * public function getLoremIpsum()
     * {
     * $ret = '';
     * $template = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ ';
     * $len = rand($this->minLength, min($this->maxLength, 50));
     * for ($a = 0; $a <= $len; $a++)
     * {
     * $ret .= $template[rand(0, strlen($template) - 1)];
     * }
     * return $ret;
     * }
     *
     * public function getDbDefinition()
     * {
     * return 'varchar(' . $this->maxLength . ') ' . 'NOT NULL';
     * }
     *
     */
}
