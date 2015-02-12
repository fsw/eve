<?php

/** @Command(helpText='runs a simple interactive PHP shell') */
class Command_Shell extends Action_Command
{

    public function run()
    {
        @ob_end_clean();
        error_reporting(E_ALL);
        set_time_limit(0);

        //ugly hack to have autocompletion for all classes
        $method = new ReflectionMethod('Eve', 'includeAll');
        $method->setAccessible(true);
        $method->invoke(null);
        
        set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../../../vendor/PHP_Shell');
        //var_dump(get_include_path()); die();
        
        Eve::requireVendor('PHP_Shell/PHP/Shell.php');
        //Eve::requireVendor('PHP_Shell/PHP/Shell/Extensions/Autoload.php');
        //Eve::requireVendor('PHP_Shell/PHP/Shell/Extensions/AutoloadDebug.php');
        Eve::requireVendor('PHP_Shell/PHP/Shell/Extensions/Colour.php');
        Eve::requireVendor('PHP_Shell/PHP/Shell/Extensions/ExecutionTime.php');
        Eve::requireVendor('PHP_Shell/PHP/Shell/Extensions/InlineHelp.php');
        Eve::requireVendor('PHP_Shell/PHP/Shell/Extensions/VerbosePrint.php');
        Eve::requireVendor('PHP_Shell/PHP/Shell/Extensions/LoadScript.php');
        Eve::requireVendor('PHP_Shell/PHP/Shell/Extensions/Echo.php');
        
        function PHP_Shell_defaultErrorHandler($errno, $errstr, $errfile, $errline, $errctx)
        {
            if (!($errno & error_reporting())) {
                return;
            }
        
            // ... what is this errno again ?
            if ($errno == 2048) {
                return;
            }
        
            throw new Exception(sprintf("%s:%d\r\n%s", $errfile, $errline, $errstr));
        }
        
        set_error_handler("PHP_Shell_defaultErrorHandler");
        
        $__shell = new PHP_Shell();
        $__shell_exts = PHP_Shell_Extensions::getInstance();
        $__shell_exts->registerExtensions(
                array(
                        "options"        => PHP_Shell_Options::getInstance(), /* the :set command */
                        //"autoload"       => new PHP_Shell_Extensions_Autoload(),
                        //"autoload_debug" => new PHP_Shell_Extensions_AutoloadDebug(),
                        "colour"         => new PHP_Shell_Extensions_Colour(),
                        "exectime"       => new PHP_Shell_Extensions_ExecutionTime(),
                        "inlinehelp"     => new PHP_Shell_Extensions_InlineHelp(),
                        "verboseprint"   => new PHP_Shell_Extensions_VerbosePrint(),
                        "loadscript"     => new PHP_Shell_Extensions_LoadScript(),
                        "echo"           => new PHP_Shell_Extensions_Echo(),
                )
        );
        
        $f = <<<EOF
PHP-Shell - Version %s%s
(c) 2006, Jan Kneschke <jan@kneschke.de>
        
>> use '?' to open the inline help
        
EOF;
        
        printf(
                $f,
                $__shell->getVersion(),
                $__shell->hasReadline() ? ', with readline() support' : ''
        );
        unset($f);
        
        print $__shell_exts->colour->getColour("default");
        while ($__shell->input()) {
            try {
                $__shell_exts->exectime->startParseTime();
                if ($__shell->parse() == 0) {
                    // we have a full command, execute it
        
                    $__shell_exts->exectime->startExecTime();
        
                    $__shell_retval = eval($__shell->getCode());
                    if (isset($__shell_retval) && $__shell_exts->echo->isEcho()) {
                        print $__shell_exts->colour->getColour("value");
        
                        if (function_exists("__shell_print_var")) {
                            __shell_print_var(
                            $__shell_retval,
                            $__shell_exts->verboseprint->isVerbose()
                            );
                        } else {
                            var_export($__shell_retval);
                        }
                    }
                    // cleanup the variable namespace
                    unset($__shell_retval);
                    $__shell->resetCode();
                }
            } catch(Exception $__shell_exception) {
                print $__shell_exts->colour->getColour("exception");
                printf(
                '%s (code: %d) got thrown'.PHP_EOL,
                get_class($__shell_exception),
                $__shell_exception->getCode()
                );
                print $__shell_exception;
        
                $__shell->resetCode();
        
                // cleanup the variable namespace
                unset($__shell_exception);
            }
            print $__shell_exts->colour->getColour("default");
            $__shell_exts->exectime->stopTime();
            if ($__shell_exts->exectime->isShow()) {
                printf(
                " (parse: %.4fs, exec: %.4fs)",
                $__shell_exts->exectime->getParseTime(),
                $__shell_exts->exectime->getExecTime()
                );
            }
        }
        
        print $__shell_exts->colour->getColour("reset");
        //proc_open('php -a');
        /*
        $descriptorspec = array(
                0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
                1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
                2 => array("pipe", "w") // stderr is a file to write to
        );
        
        $cwd = '/tmp';
        $env = array('some_option' => 'aeiou');
        
        $process = proc_open('php -a', $descriptorspec, $pipes, $cwd, $env);
        while (! feof(STDIN)) {
            fwrite($pipes[0], $this->readLine());
            while (!feof($pipes[1]))
                echo fgets($pipes[1])."<br/>";
            
        }*/
        //echo exec('php -a');
        
        //while (! feof(STDIN)) {
          //  print 'php# ';
            //eval($this->readLine());
            /* TODO a bit smarter mode
             * print empty($cmd) ? '# ' : '..';
             * $cmd .= $this->readLine() . NL;
             * ob_start();
             * if (eval($cmd) !== false) {
             * ob_end_flush();
             * $cmd = '';
             * } else {
             * ob_end_clean();
             * } */
        //}
    }
}