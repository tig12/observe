<?php
/******************************************************************************
    Initializes empty distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-12 16:07:07+01:00, Thierry Graff : Isolate this code in a separated class
    @history    2021-03-14 20:15:29+01:00, Thierry Graff : Big refactor
    @history    2021-03-10 04:31:53+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\util\distrib;

// previoulsly class degrees
class EmptyDistrib {
    
    /**
        @param  $codes1 and $codes2 are arrays containing keys
                ex: ['SO', 'MO', 'ME' ...]
        @param  $N The number of elements in the values of the result.
        @return Array initialized with compound keys.
                ex: [
                    'SO-SO' => [0 => 0, ... 359 => 0],
                    ...
                    'NN-NN' => [0 => 0, ... 359 => 0]
                ]
    **/
    public static function emptyDoubleDistrib(array &$codes1, array &$codes2, int $N = 360): array {
        $res = [];
        foreach($codes1 as $code1){
            foreach($codes2 as $code2){
                $res["$code1-$code2"] = array_fill(0, $N, 0);
            }
        }
        return $res;
    }
    
} // end class
