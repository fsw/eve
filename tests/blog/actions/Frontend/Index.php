<?php

/** @UrlName('') */
class Frontend_Index extends Frontend
{
    /** @Param(type='int', default=0) */
    public $page;
    
    public function run(){
        
        $this->posts = Post::getNewest($this->page);
        
        parent::run();
    }
    
}
