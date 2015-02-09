<?php

class Frontend extends Action_HTML5
{

    protected function getStylesheetsUrls()
    {
        return ['/assets/frontend.less'];
    }

    public function run()
    {
        $this->allCategories = Category::getAll();
        parent::run();
    }
}