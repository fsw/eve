<?php

/** @UrlName('') */
class Frontend_Index extends Frontend
{
    /** @Param(type='int', default=0) */
    public $page;
    
    protected function sectionBody(){
        
        $this->posts = Model_Post::getNewest();
        
        echo 'Hello!' . $this->page;
    }
    
    protected function sectionContent(){
        echo 'Hello!' . $this->page;
    }
}
