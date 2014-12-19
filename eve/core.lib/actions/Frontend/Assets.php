<?php

/** @UrlName('assets') */
class Frontend_Assets extends Action_Http
{

    /** @Param(fullPath=true) */
    public $path;

    public function run () {
        if (PHP_SAPI !== 'cli-server') {
            throw new Exception('Precompiled assets should be served by your webserver in production code');
        }
        $realPath = Eve::findFile('assets' . DS . $this->path);
        if (file_exists($realPath)) {
            $includePaths = [];
            foreach (Eve::getLibRoots() as $root) {
                if (Fs::isDir($root . 'assets')) {
                    $includePaths[] = $root . 'assets';
                }
            }
            header('Content-Type: text/css');
            // flags for production mode: --clean-css
            $cmd = 'lessc --include-path="' . implode(':', $includePaths) . '" --rootpath="/assets/" --relative-urls ' .
                     $realPath;
            passthru($cmd);
        } else {
            throw new NotFoundException();
        }
    }
}
