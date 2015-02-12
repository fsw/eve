<?php

/** @Command(helpText='builds production code for given configuration') */
class Command_Build extends Action_Command
{

    /** @Param(type='string', helpText='name of configuration to build', default='prod') */
    public $conf;
    /** @Param(type='string', helpText='destination folder', default='build') */
    public $outputPath;
    
    public function run()
    {
        $devLibPath = dirname(dirname(__DIR__));
        $this->outputPath = getcwd() . DS . $this->outputPath . DS;
        if (!is_dir($this->outputPath) || !is_writable($this->outputPath)) {
            echo "Destination '{$this->outputPath}' does not exist or is not writable!\n";
            return -1;
        }
        
        //determine all php classes used on production
        if (!is_dir($this->outputPath . 'php')) {
            mkdir($this->outputPath . 'php');
        }
        
        $method = new ReflectionMethod('Eve', 'includeAll');
        $method->setAccessible(true);
        $method->invoke(null);

        $allPrimitives = array_merge(get_declared_traits(), get_declared_classes(), get_declared_interfaces());
        foreach($allPrimitives as $primitive) {
            $infile = Eve::getClassFileName($primitive);
            if (($infile != null) && (strrpos($infile, $devLibPath, -strlen($infile)) === FALSE)) {
                $outfile = $this->outputPath . 'php' . DS . $primitive . '.php';
                echo "$infile => $outfile\n";
                copy($infile, $outfile);
            }
        }
        
        //copying static files
        if (!is_dir($this->outputPath . 'static')) {
            mkdir($this->outputPath . 'static');
        }
        foreach(Eve::listDir('static') as $staticFile) {
            $infile = Eve::findFile('static' . DS . $staticFile);
            $outfile = $this->outputPath . 'static' . DS . $staticFile;
            echo "$infile => $outfile\n";
            copy($infile, $outfile);
        }
        
        //building assets files
        if (!is_dir($this->outputPath . 'assets')) {
            mkdir($this->outputPath . 'assets');
        }
        foreach(Eve::listDir('assets') as $assetFile) {
            $infile = Eve::findFile('assets' . DS . $assetFile);
            list($mime, $ext, $cmd) = Assets::getMimeAndCmd($infile);
            var_dump($assetFile, $mime, $ext, $cmd);
            //$outfile = $this->outputPath . 'assets' . DS . $assetFile;
            //echo "$infile => $outfile\n";
            //copy($infile, $outfile);
        }
        
    }
}