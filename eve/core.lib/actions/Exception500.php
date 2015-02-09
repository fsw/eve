<?php

/**
 * Action.
 * 
 * @author fsw
 */
class Exception500 extends Frontend
{

    public function run()
    {
        header("HTTP/1.0 500 Internal Server Error");
        parent::run();
    }
}
