<?php

class CommonFile extends Entity {
    
    /** @Field_String(maxLength=256) */
    public $name;
    
    /** @Field_File */
    public $file;
    
    public static function getAdminColumns(){
	return ['name', 'file'];
    }
 
    public static function getPlural(){
	return 'Common Files';
    }
    
}

