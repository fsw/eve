<?php

/** @Command(helpText='this command says hello') */
class Command_Hello extends Action_Command
{
    /** @Param(type='string', helpText='your first name') */
    public $name;
    
    /** @Param(type='int', default=18, helpText='your age') */
    public $age;
    
    public function run()
    {
        print sprintf(_('Hello %s!'), $this->name) . "\n";
        print sprintf(_('I am also %d.'), $this->age) . "\n";
    }
    
}