<?php
/****************************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-04-09 22:36:58+01:00, Thierry Graff : Add to tiglib
    @history    2021-03-14 17:59:59+01:00, Thierry Graff : Creation from existing code
****************************************************************************************/

namespace tiglib\stats;

/** 
    Arithmetic mean
**/
class mean {
    
    /**
        Computes the arithmetic mean of data
        @param  $data Regular or associative array.
                Values are used to compute the mean
    **/
    public static function compute(array &$data): float {
        return array_sum($data) / count($data);
    }
    
} // end class
