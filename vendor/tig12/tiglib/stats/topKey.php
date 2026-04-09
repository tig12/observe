<?php
/******************************************************************************
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-04-09 22:41:12+01:00, Thierry Graff : Add to tiglib
    @history    2021-03-14 17:59:59+01:00, Thierry Graff : Creation from existing code
********************************************************************************/

namespace tiglib\stats;

class topKey {
    
    /**
        Computes the "top key".
        In key / value array $data, the top key is the key corresponding to the highest value
        (if several keys correspond to the same highest value, the first key is returned).
        @param  $data Associative array.
        @return Array with 2 elements :
            - the key corresponding to the highest value.
            - the place of this key in the array (0 = first key of the array...)
            Ex: $data = ['toto' => 3, 'titi' => 5, 'tata' => 5, 'tutu' => 4]
                returns ['titi', 1]
    **/
    public static function compute(&$data) {
        $max = max($data);
        $key = '';
        $index = 0;
        foreach($data as $k => $v){
            if($v == $max){
                $key = $k;
                break;
            }
            $index++;
        }
        return [$key, $index];
    }
    
} // end class
