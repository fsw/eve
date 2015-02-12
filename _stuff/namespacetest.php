<?php

namespace Site;
use Test1;
use Test2;

function my_autoloader($class) {
	var_dump($class);
    //include 'classes/' . $class . '.class.php';
}

spl_autoload_register( __NAMESPACE__ . '\my_autoloader');



$x = new Dupa();


