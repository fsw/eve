<?php

abstract class Action_Json extends Action_Http
{

    public function run()
    {
        header('Content-type: application/json');
        echo json_encode($this->post($_POST));
    }
}
