<?php

class Command_Test extends Action_Command
{
    /** Param(type='string', helpText='your first name') */
    public $name;
    
    public function run()
    {
        $this->print(_('Hello %s!'), $name);
    }
    
}