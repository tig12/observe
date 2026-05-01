<?php
/******************************************************************************
    
    Utilities to read / write distributions in CSV files.
    Handles distributions of type dim1 and dim2.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2021-02-28 23:03:00+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model\distrib;

use observe\model\Observe;

class CsvDistrib {
    
    //
    // 1 dimension associative arrays
    //
    
    /**
        Builds the content of a csv file containing a distribution
        A distribution is just an associative array $k => $v
    **/
    public static function distrib2csv_dim1(&$distrib, $sep=Observe::CSV_SEP): string {
        $res = '';
        foreach($distrib as $k => $v){
            $res .= "$k$sep$v\n";
        }
        return $res;
    }
    
    /** 
        Loads a distribution of type dim1 from a csv file.
        The csv must contain 2 columns
        The first column contains the values of the random variable
            ex: the angular values (0 - 359) in the case of a zodiacal distribution
        The second column contains the nb of occurences.
        @param  $sep        Separator used in the csv file.
    **/
    public static function csv2distrib_dim1(string  $filename, string  $sep = Observe::CSV_SEP): array {
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        $res = [];
        foreach($lines as $line){
            $tmp = explode($sep, $line);
            $res[$tmp[0]] = $tmp[1];
        }
        return $res;
    }
    
    //
    // 2 dimensions regular arrays
    //
    
    /**
        Builds the content of a csv file containing a 2-dim array
        Each line must have the same number of elements.
        Supports associative arrays, but the keys are nit used.
        Ex of valid array: [
            0 => [0 => value_0_0, ... 359 => value_0_359],
            ...
            359 => [0 => value_359_0, ... 359 => value_359_359],
        ]
    **/
    public static function distrib2csv_dim2(&$a, $sep=Observe::CSV_SEP): string {
        $res = '';
        foreach($a as $k => $v){
            $res .= implode($sep, $v) . "\n";
        }
        return $res;
    }
    
    /** 
        Loads a distribution from a csv file.
        Returns a 2-dim array ; each element contains a line of the csv, stored in an array containing columns of this line.
        @param  $sep        Separator used in the csv file.
    **/
    public static function csv2distrib_dim2(string $filename, string $sep = Observe::CSV_SEP): array {
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        $res = [];
        foreach($lines as $line){
            $res[] = explode($sep, $line);
        }
        return $res;
    }
    
    //////////// NOT USED YET IN 2026 VERSION ////////////
    
    /** 
        Returns true if a line of a distribution should be skipped.
    **/
    public static function skipLine(&$line, &$key, &$skip): bool {
        return $line[$key] == $skip;
    }
    
} // end class
