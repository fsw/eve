<?php

class MenuItem extends Entity {

    use Entity_TreeTrait;
    
    /** @Field_String */
    public $name;
    
    /** @Field_String */
    public $title;
    
    
    //TODO add relation to actions and change url field to smart getter method
    /** @Field_String */
    public $url;
    
        
    /*public static function getNavigation(){
	return self::getManyByQuery('WHERE in_navigation=1 ORDER BY `left`');
    }*/
    
    public static function getAdminColumns(){
	return ['name', 'url'];
    }
    
    public static function getPlural(){
	return 'Menu Items';
    }
    
}

