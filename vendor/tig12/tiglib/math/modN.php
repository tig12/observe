<?php
/******************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-02-25 08:00:15+01:00, Thierry Graff : Creation from mod360
********************************************************************************/

namespace tiglib\math;

class modN {
    
    //***************************************************
    /**
        Returns a number modulo $mod (between 0 and $mod).
        $nb can be integer or decimal, positive or negative
    **/
    public static function compute(float $nb, int $mod) {
        $int = floor($nb);
        $dec = $nb - $int; // decimal part
        if($nb >= 0){
            return ($int % $mod) + $dec;
        }
        else{
            $new = $int;
            while($new < 0){
                $new += $mod;
            }
            return $new + $dec;
        }
    }

} // end class
