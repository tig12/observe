<?php
/******************************************************************************
    
    Convenience function to perform file system operations with log

    @license    GPL
    @history    2021-02-27 23:39:08+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts;

class fileSystem {
    
    // ******************************************************
    /**
        file_put_contents + log
    **/
    public static function saveFile(
        string $path,
        string $content,
        string $message = '',
        bool   $verbose = true,
    ) {
        file_put_contents($path, $content);
        if($verbose){
            echo ($message != '' ? $message : "Wrote $path\n");
        }
    }
    
    // ******************************************************
    /**
        mkdir + log
        The directory is created only if it does not exist
    **/
    public static function mkdir(
        string $path,
        string $message = '',
        bool   $verbose = true,
    ) {
        if(!is_dir($path)){
            mkdir($path, 0755, true);
            if($verbose){
                echo ($message != '' ? $message : "Created directory $path\n");
            }
        }
    }
    
} // end class
