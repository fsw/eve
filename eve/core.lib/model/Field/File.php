<?php

class File
{

    private $entity;

    private $name;

    private $id;

    private $ext;

    function __construct($entity, $name, $id, $ext)
    {
        $this->entity = $entity;
        $this->name = $name;
        $this->id = $id;
        $this->ext = $ext;
    }

    function getId()
    {
        return $this->id;
    }

    function isEmpty()
    {
        return empty($this->id);
    }

    function getExt()
    {
        return $this->ext;
    }

    function getUrl()
    {
        return $this->isEmpty() ? '' : (EVE_UPLOADS_URL . $this->entity . '/' . $this->name . '_' .
                 sprintf('%04d', $this->id) . '.' . $this->ext);
    }

    function __toString()
    {
        return $this->getUrl();
    }
}

class Field_File extends Field
{

    public function updateWithPost($value, $post)
    {
        // var_dump($_FILES[$this->name]);
        if (empty($_FILES[$this->name])) {
            return $value;
        }
        if (! empty($_FILES[$this->name]['error'])) {
            if (! empty($post[$this->name . '_delete']) && ($post[$this->name . '_delete'] == 'delete')) {
                return $this->getDefault();
            }
            return $value;
            throw new Field_Exception($this->name, "Sorry no bonus here");
        }
        $tmp_path = $_FILES[$this->name]['tmp_name'];
        $ext = substr(end((explode(".", $_FILES[$this->name]['name']))), 0, 3);
        
        if (! $value->isEmpty()) {
            $new_id = $value->getId();
        } else {
            $files = glob(EVE_UPLOADS_ROOT . $this->entity . '/' . $this->name . '_*');
            natsort($files);
            $highest = end($files); // .{png,jpg,gif}
            if (! sscanf($highest, EVE_UPLOADS_ROOT . $this->entity . '/' . $this->name . '_%d', $new_id)) {
                $new_id = 111;
            } else {
                $new_id ++;
            }
        }
        
        $new_path = EVE_UPLOADS_ROOT . $this->entity . '/' . $this->name . '_' . sprintf('%04d', $new_id) . '.' . $ext;
        mkdir(dirname($new_path), 0755, true);
        
        move_uploaded_file($tmp_path, $new_path);
        // die($new_path);
        return new Image($this->entity, $this->name, $new_id, $ext);
    }

    public function getDefault()
    {
        return new File($this->entity, $this->name, 0, null);
    }

    public function fromDbRow($row)
    {
        return new File($this->entity, $this->name, $row[$this->name], $row[$this->name . '_ext']);
    }

    public function toDbRow($value)
    {
        return [$this->name => $value->getId(), $this->name . '_ext' => $value->getExt()];
    }

    public function getFormInput($value)
    {
        $ret = '<div class="clearfix">';
        $ret .= '<div class="pull-right">' . $value->getUrl() . '</div>';
        $ret .= '<input class="pull-left" type="file" name="' . $this->name . '"  />';
        $ret .= '<label class="pull-left"><input type="checkbox" name="' . $this->name .
                 '_delete" value="delete" /> delete</label>';
        $ret .= '</div>';
        return $ret;
    }

    public function getDbDefinition()
    {
        return [$this->name => 'int(11) NOT NULL', $this->name . '_ext' => 'char(3) NOT NULL'];
    }
}
