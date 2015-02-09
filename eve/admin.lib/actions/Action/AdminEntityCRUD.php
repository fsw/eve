<?php

class Action_AdminEntityCRUD extends Action_Admin
{

    public static $entityClass = '';

    /** @Param(type='string') */
    public $action;

    /** @Param(type='int', default=0) */
    public $id;

    public function run()
    {
        parent::run();
    }
}
