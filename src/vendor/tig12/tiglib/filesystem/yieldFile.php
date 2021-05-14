<?php
/******************************************************************************

    @license    GPL
    @history    2021-05-12 01:06:10+01:00, Thierry Graff : Creation from https://wiki.php.net/rfc/generators
********************************************************************************/
namespace tiglib\filesystem;


class yieldFile{
    
    /**
        Fills a csv file to an array of associative arrays.
        The first line of the array is considered as the header, containing the field names.
        All lines are upposed to have the same number of fields (no check is done).
        @param      $filename Absolute path to the csv file
        @param      $delimiter field delimiter (one character only).
        @return     false or associative array
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
    
}// end class
