<?php

/** @UrlName('static') */
class Frontend_Static extends Action_Http
{

    /** @Param(fullPath=true) */
    public $path;

    public function run()
    {
        if (PHP_SAPI !== 'cli-server') {
            throw new Exception('Static files should be served by your webserver in production code');
        }
        $realPath = Eve::findFile('static' . DS . $this->path);
        if (file_exists($realPath)) {
            header('Content-Type: ' . mime_content_type($realPath));
            readfile($realPath);
        } else {
            throw new NotFoundException();
        }
    }
}
