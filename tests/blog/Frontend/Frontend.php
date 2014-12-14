<?php
class Frontend extends Action_HTML5
{
    protected function sectionBody(){
        
        $this->posts = Model_Post::getNewest();
        
        echo 'Hello!' . $this->page;
    }
}