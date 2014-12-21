<?php

/**
 * Entity.
 * 
 * @author fsw
 */
abstract class Entity
{

    /** @Field_Id */
    public $id;

    protected $_originalRow;
    protected $_inDatabase;

    public function __construct () {
        foreach (self::getFields() as $field => $annotation) {
            $this->$field = $annotation->getDefault();
        }
        $_originalRow = null;
        $_inDatabase = false;
    }

    public function updateWithArray ($data) {
        foreach (self::getFields() as $field => $annotation) {
            if (isset($data[$field])) {
                $this->$field = $annotation->fromString($data[$field]);
            }
        }
    }
    
    public function __toString () {
        return empty($this->name) ? ('#' . $this->id) : $this->name;
    }

    protected function preSave ($oldRow, $newRow) {}

    protected function postSave ($oldRow, $newRow) {}

    public function delete () {
        if ($this->id > 0) {
            self::getDb()->delete(static::getTableName(), $this->id);
        }
    }
    
    public function validate () {
        $errors = [];
        foreach (self::getFields() as $field => $annotation) {
            $ret = $annotation->validate($this->$field);
            if ($ret !== true){
                $errors[$field] = $ret;
            }
        }
        //TODO extra validate logic
        return $errors;
    }
    
    public function save () {
        $sets = array();
        foreach (self::getFields() as $field => $annotation) {
            $sets = array_merge($sets, $annotation->toDbRow($this->$field));
        }
        $this->preSave($this->_originalRow, $sets);
        
        if ($this->_inDatabase) {
            self::getDb()->update(static::getTableName(), $this->id, $sets);
        } else {
            self::getDb()->insert(static::getTableName(), $sets);
            $this->id = self::getDb()->lastInsertId();
        }
        
        $this->postSave($this->_originalRow, $sets);
        
        return $this->id;
    }

    public static function getByUrlParam ($param) {
        return self::getOneByQuery('WHERE slug=?', [
                $param
        ]);
    }

    public static function getById ($id) {
        // TODO request cache!!!
        return self::getOneByQuery('WHERE id=?', [
                $id
        ]);
    }
    
    public static function getByFields ($fields) {
        // TODO request cache!!!
        $where = [];
        foreach ( array_keys($fields) as $key ){
            $where[] = $key . '=?';
        }
        return self::getOneByQuery('WHERE ' . implode(' AND ', $where), array_values($fields));
    }
    
    public static function getAll () {
        return self::getManyByQuery('', []);
    }

    protected static function getOneByQuery ($query, $params = []) {
        $row = self::getDb()->fetchRow(
                'SELECT * FROM ' . self::getTableName() . ' ' . $query, $params);
        if (empty($row))
            return null;
        $className = get_called_class();
        // var_dump($row);
        $ret = new $className();
        $ret->_originalRow = $row;
        $ret->_inDatabase = true;
        foreach (self::getFields() as $field => $annotation) {
            // var_dump($annotation);
            $ret->$field = $annotation->fromDbRow($row);
            // var_dump($ret->$field);
        }
        // var_dump($ret);
        return $ret;
    }

    public static function getManyByQuery ($query, $params = []) {
        // var_dump('ASDASD');
        $rows = self::getDb()->fetchAll(
                'SELECT * FROM ' . static::getTableName() . ' ' . $query, 
                $params);
        // var_dump($rows);
        $className = get_called_class();
        $ret = array();
        foreach ($rows as $row) {
            $entity = new $className();
            $entity->_originalRow = $row;
            $entity->_inDatabase = true;
            // var_dump($entity);
            foreach (self::getFields() as $field => $annotation) {
                // var_dump($field);
                $entity->$field = $annotation->fromDbRow($row);
                // var_dump($ret->$field);
            }
            $ret[] = $entity;
        }
        // var_dump($ret);
        return $ret;
    }

    public static function getAdminColumns () {
        return array_keys(static::getFields());
    }

    public static function getAdminFields () {
        return array_keys(static::getFields());
    }

    public static function getField ($key) {
        return static::getFields()[$key];
    }

    protected static function getFields () {
        // TODO cache this!!!!
        // var_dump("ASDASDXXXX", get_called_class());
        $ret = array();
        foreach (Eve::getFieldsAnnotations(get_called_class()) as $field => $annotations) {
            // var_dump("33333");
            foreach ($annotations as $annotation) {
                // var_dump($annotation);
                if ($annotation instanceof Field) {
                    // var_dump($annotation);
                    $annotation->name = $field;
                    $annotation->entity = get_called_class();
                    $ret[$field] = $annotation;
                }
            }
        }
        return $ret;
    }

    public static function getPlural () {
        //TODO cache forever
        return EnglishPluralizer::pluralize(get_called_class());
    }

    protected static function getTableName () {
        return strtolower(str_replace(' ', '_', static::getPlural()));
    }

    private static $db;

    /**
     * 
     * @return Db */
    final protected static function getDb () {
        if (empty(self::$db)) {
            self::$db = new Db(Eve::setting('db'));
        }
        return self::$db;
    }
}
