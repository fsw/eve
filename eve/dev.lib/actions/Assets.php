<?php

class Assets extends Action_Http
{

    /** @Param(fullPath=true) */
    public $path;

    public static function getMimeAndCmd($fullPath, $productionMode = false)
    {
        $includePaths = [];
        foreach (Eve::getLibRoots() as $root) {
            if (Fs::isDir($root . 'assets' . DS . 'includes')) {
                $includePaths[] = $root . 'assets' . DS . 'includes';
            }
        }
        $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
        $mime = 'unknown';
        $cmd = 'echo "ERROR"';
        switch ($ext)
        {
            case "js":
                $mime = "application/javascript";
                break;
            case "less":
                $mime = 'text/css';
                $ext = 'css';
                // flags for production mode: --clean-css
                $cmd = 'lessc --include-path="' . implode(':', $includePaths) . '" --rootpath="/assets/" --relative-urls ' . $fullPath;
                break;
            case "json":
                $mime = "application/json";
                break;
            case "xml":
                $mime = "application/xml";
                break;
            case "jpg":
            case "jpeg":
            case "jpe":
                $mime = "image/jpg";
                break;
            case "png":
            case "gif":
            case "bmp":
                $mime =  "image/" . $ext;
                break;
            default:
                $mime = mime_content_type($realPath);
                break;
        }
        
        return array($mime, $ext, $cmd);
    }
    
    public function run()
    {
        if (PHP_SAPI !== 'cli-server') {
            throw new Exception('Precompiled assets should be served by your webserver in production code');
        }
        $fullPath = Eve::findFile('assets' . DS . $this->path);
        if (file_exists($fullPath)) {
            list($mime, $ext, $cmd) = static::getMimeAndCmd($fullPath);
            header('Content-Type: ' . $mime);
            passthru($cmd);
        } else {
            throw new NotFoundException();
        }
    }
}
