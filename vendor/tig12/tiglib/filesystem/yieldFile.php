<?php
/******************************************************************************
    
    Reads a file line by line.
    Using yield permits to avoid loading the whole file in memory.
    Usage:
        $file = 'toto.txt'; // or 'compress.bzip2:///path/tot/toto.txt.bz2'
        foreach(yieldFile::loop($file) as $line){}
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2021-05-12 01:06:10+01:00, Thierry Graff : Creation from https://wiki.php.net/rfc/generators
********************************************************************************/
namespace tiglib\filesystem;

class yieldFile{
    
    /**
        Generator function to read a file line by line.
        @param      $filename Absolute path to the file.
    **/
    public static function loop($filename){
        if (!$fileHandle = fopen($filename, 'r')) {
            return false;
        }
        while (false !== $line = fgets($fileHandle)) {
            yield $line;
        }
        fclose($fileHandle);
    }
    
} // end class
