<?php
/******************************************************************************
    Recursively delete a directory
    Adaptation of example code found at
    https://www.php.net/manual/en/function.rmdir.php
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2021-02-08 17:14:42+01:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\filesystem;

class rrmdir {
    
   public static function execute($dir) {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::execute("$dir/$file") : unlink("$dir/$file"); // recursive here
        }
        return rmdir($dir);
    }
    
}
