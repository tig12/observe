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
        The first column contains the values of the random variable
            ex: the angular values (1 - 360) in the case of a zodiacal distribution
        The second column contains the nb of occurences
        @param  $header True if the first line contains column line ; false otherwise
    **/
    public static function loadFromCSV(string $filename, bool $header): array {
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
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
    
    // ******************************************************
    /**
        Computes the arithmetic mean of data
        @param  $data Regular or associative array.
                Values are used to compute the mean
    **/
    public static function mean(&$data) {
        return array_sum($data) / count($data);
    }
    
    
} // end class
