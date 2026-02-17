<?php
/** 
    Unique autoload code to include
    Contains PSR-4 autoload for namespace "observe"
    and inclusion of autoload for vendor code.
    
    @history    2020-12-15 21:39:47+01:00, Thierry Graff : Creation 
**/

/** 
    Autoload for vendor code
**/
$rootdir = dirname(dirname(__DIR__));
require_once implode(DS, [$rootdir, 'vendor', 'tig12', 'tiglib', 'autoload.php']);
require_once implode(DS, [$rootdir, 'vendor', 'tig12', 'tigeph', 'php', 'autoload.php']);
require_once implode(DS, [$rootdir, 'vendor', 'tig12', 'tigdraw', 'autoload.php']);

/** 
    Autoload for observe namespace
**/
spl_autoload_register(
    function ($full_classname){
        $namespace = 'observe';
        if(strpos($full_classname, $namespace) !== 0){
            return; // not managed by this autoload
        }
        $root_dir = dirname(__DIR__); // root dir for this namespace
        $classname = str_replace($namespace . '\\', '', $full_classname);
        $classname = str_replace('\\', DS, $classname);
        $filename = $root_dir . DS . $classname . '.php';
// echo "filename = $filename\n";
// echo "classnama = $classname\n";
        $ok = include_once($filename);
        if(!$ok){
            throw new \Exception("AUTOLOAD FAILS for class $full_classname");
        }
    }
);
