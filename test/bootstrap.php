<?php
/******************************************************************************

    Boostrap for phpunit.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-04-22 15:28:49+02:00, Thierry Graff : Creation
********************************************************************************/

// Autoload for general observe namespace
require_once implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'src', 'app', 'init.php']);

// Autoload for observe\test
spl_autoload_register(
    function ($full_classname){
        $namespace = 'observe\\test';
        if(strpos($full_classname, $namespace) !== 0){
            return; // not managed by this autoload
        }
        $root_dir = __DIR__; // root dir for this namespace
        $classname = str_replace($namespace . '\\', '', $full_classname);
        $classname = str_replace('\\', DS, $classname);
        $filename = $root_dir . DS . $classname . '.php';
        $ok = include_once($filename);
        if(!$ok){
            throw new \Exception("AUTOLOAD FAILS for class $full_classname");
        }
    }
);


