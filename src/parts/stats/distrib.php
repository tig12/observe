<?php
/******************************************************************************
    
    Utilities to manage distribution
    A distribution is a key - value array.
    ex : ['1978' => 2563]
    Values = nb of occurence of the corresponding key
    
    @license    GPL
    @history    2021-02-28 23:03:00+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\stats;

use observe\app\Observe;

class distrib {
    
    /** 
        Loads a distribution from a csv file.
        The csv must contain 2 columns
        @param  $header True if the first line contains column line ; false otherwise
    **/
    public static function loadFromCSV(string $filename, bool $header): array {
        $lines = file($filename);
        if($header){
            array_shift($lines);
        }
        $res = [];
        foreach($lines as $line){
            $tmp = explode(Observe::CSV_SEP, $line);
            $res[$tmp[0]] = $tmp[1];
        }
        return $res;
    }
    
} // end class
