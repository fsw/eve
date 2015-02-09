<?php

class Field_ManyRelation extends Field
{

    public $to;
    public $minCount;
    public $maxCount;
    
    public function getDefault() {
        return [];
    }
    
    public function getFormInput ($value) {
        $ret = '<select id="' . $this->name . '" multiple="true" name="' . $this->name . '" class="form-control input-md"' .
                 ($this->isRequired() ? ' required=""' : '') . '>';
        foreach (call_user_func(array(
                ($this->to === 'self') ? $this->entity : $this->to,
                'getAll'
        )) as $related) {
            $name = (string) $related;
            // TODO if tree trait
            if (! empty($related->level)) {
                $name = str_repeat('&nbsp;&nbsp;&nbsp;', $related->level) . $name;
            }
            $ret .= '<option value="' . $related->id . '" ' . ($value->id == $related->id ? ' selected="selected"' : '') .
                     '>' . $name . '</option>';
        }
        $ret .= '</select>';
        return $ret;
    }

    public function updateWithPost ($value, $post) {
        return []; //isset($_POST[$this->name]) ? new Entity_Promise(($this->to === 'self') ? $this->entity : $this->to, $post[$this->name]) : $value;
    }
    
    public function fromString ($string) {
        return []; //new Entity_Promise(($this->to === 'self') ? $this->entity : $this->to, (int)$string);
    }
    
    public function fromDbRow ($row) {
        return []; //new Entity_Promise(($this->to === 'self') ? $this->entity : $this->to, $row[$this->name . '_id']);
    }

    public function toDbRow ($value) {
        return [];
    }

    public function getDbDefinition () {
        return [];
    }
}
