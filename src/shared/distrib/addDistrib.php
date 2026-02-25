<?php
/******************************************************************************
    Adds two distributions sharing the same structure    
    @license    GPL
    @history    2021-02-28 23:03:00+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\shared\distrib;

class addDistrib {
    
    /**
        Adds 2 distributions.
        @param  $d1 and $d2 must have the same structure.
                ex: [
                    'SO-SO' => [0 => 1230, ... 359 => 1342],
                    ...
                    'NN-NN' => [0 => 1158, ... 359 => 1356]
                ]
        @return Distribution containing the sum of $d1 and $d2
    **/
    public static function compute(array &$d1, array &$d2): array {
        $res = [];
        $codes = array_keys($d1);
        foreach($codes as $code){ // $code = 'SO-SO' etc.
            $res[$code] = [];
            foreach($d1[$code] as $k => $v){ // here $k = 0 ... 359
                $res[$code][$k] = $d1[$code][$k] + $d2[$code][$k];
            }
        }
        return $res;
    }
    
    /* 
    // useful only during dev, not called anymore
    private static function test_addDistrib() {
        $d1 = [
            'SO-SO' => [0 => 3, 1 => 4, 2 => 5],
            'SO-MO' => [0 => 13, 1 => 14, 2 => 15],
        ];
        $d2 = [
            'SO-SO' => [0 => 23, 1 => 24, 2 => 25],
            'SO-MO' => [0 => 33, 1 => 34, 2 => 35],
        ];
        $d3 = self::addDistrib($d1, $d2);
        print_r($d3); exit;
    }
    */
    
} // end class
