<?php
/** 
    Unique autoload code to include
    Contains PSR-4 autoload for namespace "distrib"
    and inclusion of autoload for vendor code.
    
    @history    2020-12-15 21:39:47+01:00, Thierry Graff : Creation 
**/

// autoload for vendor code
$rootdir = dirname(dirname(__DIR__));
require_once implode(DS, [$rootdir, 'vendor', 'tig12', 'tiglib', 'autoload.php']);
require_once implode(DS, [$rootdir, 'vendor', 'tig12', 'swetest-php', 'autoload.php']);

/** 
    Autoload for distrib namespace
**/
spl_autoload_register(
    function ($full_classname){
        $namespace = 'distrib';
        if(strpos($full_classname, $namespace) !== 0){
            return; // not managed by this autoload
        }
        $root_dir = dirname(__DIR__); // root dir for this namespace
        $classname = str_replace($namespace . '\\', '', $full_classname);
        $classname = str_replace('\\', DS, $classname);
        $filename = $root_dir . DS . $classname . '.php';
        $ok = include_once($filename);
        if(!$ok){
            throw new \Exception("AUTOLOAD FAILS for class $full_classname");
        }
    }
);
