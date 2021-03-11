<?php
/******************************************************************************
    
    @license    GPL
    @history    2021-03-09 10:09:46+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\astro;

use tiglib\math\mod360;

class aspects {
    
    /** 
        Computes the aspects within one data set.
        Useless aspects are not computed (eg sun-sun, or sun-moon then not moon-sun).
        
        @param  $data Array of associative arrays.
                Each assoc. array must have keys = planet codes (IAA) and values = longitudes
        @param  $cols Must contain the keys of $data for which aspects must be computed
        @param  $skip If a line containing this value is found, the resulting line will contain only empty strings
        @param  $precision Nb of decimal digits of the aspects
        @return An array of associative arrays.
                Keys of each assoc. array are composed by the planet codes used to form the aspects.
                For ex, SO-MO contains sun - moon aspects etc.
    **/
    public static function computeSingle(
        &$data,
        $cols,
        $skip=false,
        $precision,
    ) {
        $res = [];
        $keys = array_keys($data[0]);
        $N = count($data);
        $NKeys = count($keys);
        if($skip !== false){
            $emptyLine = array_fill_keys($keys, '');
        }

        foreach($data as $line){
            if($skip !== false && $line == $emptyLine){
                $res[] = $emptyLine;
                continue;
            }
            $new = [];
            for($i=0; $i < $NKeys; $i++){
                for($j=$i+1; $j < $NKeys; $j++){
                    $new[$keys[$i] . '-' . $keys[$j]] = round(mod360::compute($line[$keys[$i]] - $line[$keys[$j]]), $precision);
                }
            }
            $res[] = $new;
        }
        return $res;
    }
    
    /** 
        Computes the aspects between 2 data sets.
        $data1 and $data2 must have the same structure : see parameter $data of {@link computeSingle()} 
        Each element must be an assoc. array with keys = planet codes (IAA) and values = longitudes
        @param  $cols1 must contain the keys of $data1 for which aspects must be computed
        @param  $cols2 - same as $cols1 for $data2
        @param  $sameData Boolean indicating if $data1 = $data2
                If true, useless aspects are not computed (eg sun-sun, or sun-moon the not moon-sun)
    **/
    public static function computeSynastry(
        &$data1,
        &$data2,
        $cols1,
        $cols2,
    ) {
    
    }
    
} // end class
