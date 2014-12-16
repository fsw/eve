<?php

class Frontend extends Action_HTML5
{
    protected function getStylesheetsUrls(){
        return [
                '/assets/frontend.css',
                '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'
                ];
    }
    
    public function run(){
        $this->allCategories = Category::getAll();
        $this->title = 'Blah Blah Blah';
        parent::run();
    }
}