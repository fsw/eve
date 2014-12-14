<?php
/**
 * Action.
 * 
 * @author fsw
 */

abstract class Action_Command
{	
	
    protected function readLine()
    {
        $line = trim(fgets(STDIN)); // reads one line from STDIN
        fscanf(STDIN, "%d\n", $number); // reads number from STDIN
    } 
    
    public function executeByArgv($args)
	{
	    foreach (Eve::getFieldsAnnotations($this) as $field => $annotations) {
	        //TOIDO set options
	        /*foreach ($annotations as $annotation) {
	            if ($annotation instanceof Param) {
	                if ($annotation->value == 'int') {
	                    $action->$field = (int) array_shift($bits);
	                } elseif ($annotation->value == 'string') {
	                    $action->$field = array_shift($bits);
	                } elseif (is_subclass_of($annotation->value, Entity)) {
	                    $action->$field = call_user_func(array(
	                            $annotation->value,
	                            'getByUrlParam'
	                    ), array_shift($bits));
	                    if (empty($action->$field)) {
	                        throw new NotFoundException();
	                    }
	                } else {
	                    throw new NotFoundException();
	                }
	            }
	        }*/
	    }
	    //if (!empty($bits)) {
	    //    throw new NotFoundException();
	    //}
	    $this->run();
	    
	}
}
