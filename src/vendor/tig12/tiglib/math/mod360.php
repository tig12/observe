<?php
/******************************************************************************


    WARNING : works for positive numbers, but negative numbers must be tested and debugged
    (see unit test with -360)

    @license    GPL
    @history    2021-02-28 10:18:57+01:00, Thierry Graff : Creation from old code
********************************************************************************/

namespace tiglib\math;

class mod360 {
    
    //***************************************************
    /**
        Returns a number modulo 360 (between 0 and 360).
        $nb can be integer or decimal, positive or negative
    **/
    public static function compute($nb){
        if($nb >= 0){
            $dec = $nb - floor($nb);
            return $nb%360 + $dec;
        }
        else{
            $dec = $nb - floor($nb);
            if($dec != 0){
                $dec -= 1;
            }
            return $nb%360 + $dec + 360;
        }
    }

} // end class
