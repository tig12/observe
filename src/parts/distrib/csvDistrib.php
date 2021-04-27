<?php
/******************************************************************************
    
    Utilities to manage distribution
    A distribution is a key - value array.
    ex : ['1978' => 2563]
    Values = nb of occurence of the corresponding key
    
    @license    GPL
    @history    2021-02-28 23:03:00+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\distrib;

class csvDistrib {
    
    /**
        Builds the content of a csv file containing a distribution
        A distribution is just an associative array $k => $v
    **/
    public static function distrib2csv(&$distrib, $sep=';'): string {
        $res = '';
        foreach($distrib as $k => $v){
            $res .= "$k$sep$v\n";
        }
        return $res;
    }
    
    /** 
        Loads a distribution from a csv file.
        The csv must contain 2 columns
        The first column contains the values of the random variable
            ex: the angular values (1 - 360) in the case of a zodiacal distribution
        The second column contains the nb of occurences
        @param  $header True if the first line contains column line ; false otherwise.
        @param  $sep    Separator used in the csv file.
    **/
    public static function csv2distrib(
            string  $filename,
            bool    $header,
            string  $sep = ';',
    ): array {
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        if($header){
            array_shift($lines);
        }
        $res = [];
        foreach($lines as $line){
            $tmp = explode($sep, $line);
            $res[$tmp[0]] = $tmp[1];
        }
        return $res;
    }
    
    /** 
        Returns true if a line of a distribution should be skipped.
    **/
    public static function skipLine(&$line, &$key, &$skip): bool {
        return $line[$key] == $skip;
    }
    
} // end class
