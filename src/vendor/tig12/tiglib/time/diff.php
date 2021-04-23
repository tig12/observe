<?php
/******************************************************************************
    
    Computes difference between 2 dates.
    
    @todo       Unit test
    
    @license    GPL
    @history    2021-02-25 21:36:31+01:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\time;

class diff {
    
    /**
        Returns the duration from $d1 to $d2 (computes $d2 - $d1)
        @pre    $d1 < $d2
        @param  $unit   'Y' or 'M'
        @param  $precision Nb of decimal digits of the result 
        @return Decimal or integer number
    **/
    public static function compute(\DateTime $d1, \DateTime $d2, $unit='Y', $precision=0) {
        $y = $d1->diff($d2)->format('%r%a') / 365.25;
        if($unit == 'Y'){
            $res = $y;
        }
        else if($unit == 'M'){
            $res = $y * 12;
        }
        else throw new \Exception("tiglib\\time::diff() : Invalid parameter unit: '$unit'");
        return round($res, $precision);
    }
    
} // end class
