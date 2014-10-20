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
    
    public function __construct(){
        foreach(self::getFields() as $field => $annotation){ 
            $this->$field = $annotation->getDefault();
        }
        $_originalRow = null;
    }
    
    public function __toString() {
	return empty($this->name) ? ('#' . $this->id ) : $this->name;
    }
    
    protected function preSave($oldRow, $newRow){
	
    }
    
    protected function postSave($oldRow, $newRow){
	
    }
    
    public function save(){
	
	$sets = array();
	foreach(self::getFields() as $field => $annotation){ 
            $sets = array_merge($sets, $annotation->toDbRow($this->$field));
        }
        //var_dump($sets); die();
        $this->preSave($this->_originalRow, $sets);
        
	if ($this->id > 0){
	    self::getDb()->update(self::getTableName(), $this->id, $sets);
	} else {
	    self::getDb()->insert(self::getTableName(), $sets);
	    $this->id = self::getDb()->lastInsertId();
	}
	
        $this->postSave($this->_originalRow, $sets);
	
	return $this->id;
	
    }
    
    public static function getByUrlParam($param){
        return self::getOneByQuery('WHERE slug=?', [$param]);
    }
    
    public static function getById($id){
	//TODO request cache!!!
        return self::getOneByQuery('WHERE id=?', [$id]);
    }
    
    public static function getAll(){
        return self::getManyByQuery('', []);
    }

    protected static function getOneByQuery($query, $params = []){
        $row = self::getDb()->fetchRow('SELECT * FROM ' . self::getTableName() . ' ' . $query, $params);
        if (empty($row))
	  return null;
        $className = get_called_class();
        //var_dump($row);   
        $ret = new $className();
        $ret->_originalRow = $row;
        foreach(self::getFields() as $field => $annotation){ 
            //var_dump($annotation);               
            $ret->$field = $annotation->fromDbRow($row);
            //var_dump($ret->$field);               
        }
        //var_dump($ret);   
        return $ret;
    }
    
    public static function getManyByQuery($query, $params = []){
	//var_dump('ASDASD');
        $rows = self::getDb()->fetchAll('SELECT * FROM ' . self::getTableName() . ' ' . $query, $params);
        //var_dump($rows);
        $className = get_called_class();
        $ret = array();
        foreach($rows as $row){
	  $entity = new $className();
	  //var_dump($entity);
	  foreach(self::getFields() as $field => $annotation){ 
	      //var_dump($field);
	      $entity->$field = $annotation->fromDbRow($row);
	      //var_dump($ret->$field);               
	  }
	  $ret[] = $entity;
        }
        //var_dump($ret);   
        return $ret;
    }
  
  
    public static function getAdminColumns(){
	return array_keys(static::getFields());
    }
    
    public static function getAdminFields(){
	return array_keys(static::getFields());
    }

    protected static function getFields(){
	//var_dump("ASDASDXXXX", get_called_class());
        $ret = array();
        foreach(Eve::getFieldsAnnotations(get_called_class()) as $field => $annotations){ 
	    //var_dump("33333");
            foreach($annotations as $annotation){
		//var_dump($annotation);
                if ($annotation instanceof Field){ 
		    //var_dump($annotation);
                    $annotation->name = $field;
                    $annotation->entity = get_called_class();
                    $ret[$field] = $annotation;
                }
            }
        }
        return $ret;
    }
    
    public static function getPlural(){
	return get_called_class() . 's';
    }

    protected static function getTableName(){
	return strtolower(str_replace(' ', '_', static::getPlural()));
    }
    
    private static $db;
		
    /**
     * @return Db
     */
    final protected static function getDb()
    {
        if (empty(self::$db)) {
	    self::$db = new Db(['dsn' => EVE_DB_DSN, 'user' => EVE_DB_USER, 'pass' => EVE_DB_PASS]);
	}
	return self::$db;	
    }
    
    
}
