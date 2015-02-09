<?php

/**
 * Action.
 * 
 * @author fsw
 */
class Exception404 extends Frontend
{

    public function run()
    {
        header("HTTP/1.0 404 Not Found");
        parent::run();
    }
}
