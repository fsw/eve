<?php

class Field_Relation extends Field {

        public $toEntity;

        public function getFormInput($value){
		$ret = '<select id="' . $this->name . '" name="' . $this->name . '" class="form-control input-md"'. ( $this->isRequired() ? ' required=""' : '').'>';
		if (!$this->isRequired()) {
		      $ret .= '<option value="0">-- None --</option>';
		}
		foreach (call_user_func(array(($this->toEntity === 'self') ? $this->entity : $this->toEntity,'getAll')) as $related){
		      $name = (string)$related;
		      //TODO if tree trait
		      if (!empty($related->level)){
			  $name = str_repeat('&nbsp;&nbsp;&nbsp;', $related->level) . $name;
		      }
		      $ret .= '<option value="' . $related->id . '" '. ($value->id == $related->id ? ' selected="selected"' : '') .'>' . $name . '</option>';
		}
		$ret .= '</select>';
		return $ret;
        }
        
	public function updateWithPost($value, $post){
		return isset($_POST[$this->name]) ? new Entity_Promise(($this->toEntity === 'self') ? $this->entity : $this->toEntity, $post[$this->name]) : $value;
        }
        
	public function fromDbRow($row){
		return new Entity_Promise(($this->toEntity === 'self') ? $this->entity : $this->toEntity, $row[$this->name . '_id']);
        }
	
	public function toDbRow($value){
		return [$this->name . '_id' => $value->id];
        }
        
        public function getDbDefinition()
	{
		return [$this->name . '_id' => 'int(11) ' . ($this->isRequired() ? 'NOT NULL' : 'DEFAULT NULL')];
	}
}
