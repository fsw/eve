<?php

class Admin extends Entity {

    /** @Field_String */
    public $email;
    
    /** @Field_String */
    public $password;
    
    
    public static function login($email, $password){
      return static::getOneByQuery('WHERE email=? AND password=?', [$email, md5($password)]);
    }
    
}

