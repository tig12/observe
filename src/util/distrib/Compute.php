<?php
/******************************************************************************
    Converts data to distributions
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-12 16:07:07+01:00, Thierry Graff : Isolate this code in a separated class
    @history    2021-03-14 20:15:29+01:00, Thierry Graff : Big refactor
    @history    2021-03-10 04:31:53+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\util\distrib;

// previoulsly class degrees
class Compute {
    
    /**
        @param $data    Array of associative arrays containing 0-360 coordinates,
                        or other 0-360 values (like aspect values).
                        Each element must contain the same keys
                        Ex : [
                                0 => [
                                    'SO' => 302.524,
                                    'MO' => 49.212,
                                    ...
                                ],
                                ...
                        ]
        @return The distributions
                ex: [
                    'SO' => [ 0 => 1273, ... 359 => 1324 ],
                    'MO' => [ 0 => 1142, ... 359 => 1154 ],
                    ...
                ]
    **/
    public static function computeDistrib(&$data, $N = 360){
        $allDegrees = array_fill_keys(range(0, $N - 1), 0); // [0 => 0, 1 => 0, ... 359 => 0]
        // all $data elements have the same keys => array_keys($data[0]) is ok for all.
        $keys = array_keys($data[0]);
        $res = array_fill_keys($keys, $allDegrees);
        foreach($data as $line){
            foreach($line as $key => $lg){ // key = planet code or aspect code
                if($line[$keys[0]] == ''){
                    continue;
                }
                $value = floor($lg); // HERE floor() => 0 - 359
                if($value == 360){
                    $value = 0;
                }
                $res[$key][$value]++;
            }
        }
        return $res;
    }
    
} // end class
