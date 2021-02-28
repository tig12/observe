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
    public static function saveFile($path, $content, $message='') {
        file_put_contents($path, $content);
        echo ($message != '' ? $message : "Wrote $path\n");
    }
    
    // ******************************************************
    /**
        mkdir + log
        The directory is created only if it does not exist
    **/
    public static function mkdir($path, $message='') {
        if(!is_dir($path)){
            mkdir($path, 0755, true);
            echo ($message != '' ? $message : "Created directory $path\n");
        }
    }
    
} // end class
