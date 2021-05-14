<?php
/******************************************************************************

    @license    GPL
    @history    2021-05-12 02:06:50+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tiglib\arrays;


class yieldCsv{
    
    /**
        Transmits the yield from tiglib.filesystem.yieldFile
        
        Fills a csv file to an array of lines associative arrays.
        
        All lines are upposed to have the same number of fields (no check is done).
        @param      $filename Absolute path to the csv file
    **/
    public static function loop($filename, $separator=';'){
        if (!$fileHandle = fopen($filename, 'r')) {
            return false;
        }
        while (false !== $line = fgets($fileHandle)) {
            $tmp = explode($separator, $line);
            yield $tmp;
        }
        fclose($fileHandle);
    }
    
}// end class
