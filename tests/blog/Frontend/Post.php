<?php

/** @UrlName('post') */
class Frontend_Post extends Frontend
{
    /** @Param(type='Post') */
    public $post;
    
    
    protected function sectionContent(){
         echo 'Hello!';
    }
}