<?php
/******************************************************************************
    Adaptation of  https://stackoverflow.com/questions/12109042/php-get-file-listing-including-sub-directories
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2020-05-18 10:38:54+02:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\filesystem;

class globRecursive {
    
    /** 
        Like glob() but also scans subdirectories
        Does not support flag GLOB_BRACE
        ex: globRecursive::execute(mydir . '*' . DIRECTORY_SEPARATOR . '*.yml');
        @param  $pattern - same as php function glob()
        @param  $flag    - same as php function glob()
    **/
    public static function execute(string $pattern, int $flags = 0){
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir){
            $files = array_merge($files, self::execute($dir.DIRECTORY_SEPARATOR.basename($pattern), $flags)); // recursive here
        }
        return $files;
    }    

} // end class
