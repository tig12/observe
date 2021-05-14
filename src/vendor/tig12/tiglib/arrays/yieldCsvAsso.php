<?php
/******************************************************************************

    @license    GPL
    @history    2021-05-12 02:19:55+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tiglib\arrays;

class yieldCsvAsso {
    
    /**
        Yields line by line a csv file.
        The first line of the array is considered as the header, containing the field names.
        Each line is yielded as an array [field name => value, ...]
        All lines are supposed to have the same number of fields (no check is done).
        @param  $filename Absolute path to the csv file
        @param  $sep Separator
    **/
    public static function loop($filename, $sep=';'){
        if (!$fileHandle = fopen($filename, 'r')) {
            return false;
        }
        // first line, field names
        $line = fgets($fileHandle);
        $fieldnames = array_map('trim', explode($sep, $line));
        
        while (false !== $line = fgets($fileHandle)) {
            $tmp = explode($sep, $line);
            $res = [];
            for($i=0; $i < count($tmp); $i++){
                $res[$fieldnames[$i]] = $tmp[$i];
            }
            yield $res;
        }
        fclose($fileHandle);
    }
    
}// end class
