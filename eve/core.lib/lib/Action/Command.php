<?php
/**
 * Action.
 * 
 * @author fsw
 */

abstract class Action_Command
{	

    public static function getShortName()
    {
        return lcfirst(str_replace('Command_', '', get_called_class()));
    }
    
	public static function getShortHelp()
    {
        $callName = self::getShortName();
        $helpText = 'no description available';
        foreach (Eve::getClassAnnotations(get_called_class()) as $annotation) {
            if ($annotation instanceof Command) {
                $helpText = $annotation->helpText;
            }
        }
        foreach (Eve::getFieldsAnnotations(get_called_class()) as $field => $annotations) {
            foreach ($annotations as $annotation) {
	            if ($annotation instanceof Param) {
	                if ($annotation->default === null) {
	                    $callName .= ' <' . $field . '>';
	                }
	            }
	        }
        }
        return "\t$callName : $helpText\n";
    }
    
    public static function printFullHelp()
    {
        $callName = self::getShortName();
        $helpText = 'no description available';
        foreach (Eve::getClassAnnotations(get_called_class()) as $annotation) {
            if ($annotation instanceof Command) {
                $helpText = $annotation->helpText;
            }
        }
        foreach (Eve::getFieldsAnnotations(get_called_class()) as $field => $annotations) {
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Param) {
                    if ($annotation->default === null) {
                        $callName .= ' <' . $field . '>';
                        $arguments['<' . $field . '>'] = $annotation->helpText;
                    } else {
                        $arguments['--' . $field . '=<' . $field . '>'] = '(optional) ' . $annotation->helpText;
                    }
                }
            }
        }
        print "usage: $callName\n";
        print "description: \n";
        print "\t$helpText\n";
        print "arguments:\n";
        foreach ($arguments as $name => $help) {
            print "\t$name : $help\n";
        }
    }
    
    public function executeByArgv($args)
	{
	    $positional = [];
	    $named = [];
	    while (($arg = array_shift($args)) !== null) {
	        if (strpos($arg, '--') === 0) {
	            if (strpos($arg, '=')) {
	               list($name, $value) = explode('=', $arg, 2); 
	               $named[substr($name, 2)] = $value;
	            } else { 
	               $named[substr($arg, 2)] = true;
	            }
	        } else {
	            $positional[] = $arg;
	        }
	    }
	    foreach (Eve::getFieldsAnnotations($this) as $field => $annotations) {
	        foreach ($annotations as $annotation) {
	            if ($annotation instanceof Param) {
	                if ($annotation->default === null) {
	                    if (empty($positional)) {
	                        self::printFullHelp();
	                        die(1);
	                    }
	                    $this->$field = array_shift($positional);
	                } else {
	                    if (isset($named[$field])) {
	                        $this->$field = $named[$field];
	                        unset($named[$field]);
	                    } else {
	                        $this->$field = $annotation->default;
	                    }  
	                }
	            }
	        }
	    }
	    if (!empty($positional) || !empty($named)) {
	        self::printFullHelp();
            die(1);
	    }
	    $this->run();
	    
	}
    
    protected function readLine()
    {
        $line = trim(fgets(STDIN)); // reads one line from STDIN
        fscanf(STDIN, "%d\n", $number); // reads number from STDIN
    }
}
