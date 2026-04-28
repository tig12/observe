<?php
/******************************************************************************
    Like mkdir() but echoes a message if the directory is created on disk.
    
    @license    GPL
    @history    2020-05-18 10:38:54+02:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\filesystem;

class mkdir {
    
    public static function execute(string $dir, $permissions = 0755, $recursive = true): void {
        if(!is_dir($dir)){
            mkdir($dir, $permissions, $recursive);
            echo "Created directory $dir\n";
        }
    }    

}// end class
