<?php

/** @Command(helpText='describes <action> in detail') */
class Command_Help extends Action_Command
{

    /** @Param(type='string', helpText='name of action to describe') */
    public $action;

    public function run()
    {
        $actionClass = 'Command_' . ucfirst($this->action);
        if (! Eve::classExists($actionClass)) {
            print 'there is no such action ' . $this->action . "\n";
            die(1);
        }
        $actionClass::printFullHelp();
    }
}
