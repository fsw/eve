<?php

/** @UrlName('') */
class Frontend_Index extends Frontend
{
    /** @Param(type='Category', default='food') */
    public $category;
    
    /** @Param(type='int', default=0) */
    public $page;
    
    
    public function run(){
        
        $this->posts = Post::getNewest($this->page);
        
        parent::run();
    }
    
}
