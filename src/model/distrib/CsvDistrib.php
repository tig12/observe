<?php
/******************************************************************************
    Utilities to read / write distributions in CSV files.
    A distribution is just an associative array key - value.
    ex : ['1978' => 2563]
    Values = nb of occurence of the corresponding key
    
    @license    GPL
    @history    2021-02-28 23:03:00+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model\distrib;

use observe\model\Observe;

class CsvDistrib {
    
    /**
        Builds the content of a csv file containing a distribution
        A distribution is just an associative array $k => $v
    **/
    public static function distrib2csv(&$distrib, $sep=Observe::CSV_SEP): string {
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
            ex: the angular values (0 - 359) in the case of a zodiacal distribution
        The second column contains the nb of occurences.
        @param  $has_header true if the first line of the distribution contained in the csv file is a header (= a line containing the titles of the columns) ; false otherwise.
        @param  $sep        Separator used in the csv file.
    **/
    public static function csv2distrib(
            string  $filename,
            bool    $has_header,
            string  $sep = Observe::CSV_SEP,
    ): array {
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        if($has_header){
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
