<?php
/******************************************************************************
    
    Computes difference between 2 dates.
    
    @todo       Unit test
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2021-02-25 21:36:31+01:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\time;

class diff {
    
    /**
        Returns the duration from $d1 to $d2 (computes $d2 - $d1)
        @pre    $d1 < $d2
        @param  $unit   'D', 'M' or 'Y'
        @param  $precision Nb of decimal digits of the result 
        @return Decimal or integer number
    **/
    public static function compute(\DateTime $d1, \DateTime $d2, $unit='Y', $precision=0) {
        // %r: - 1 if < 0 ; empty if >= 0
        // %a: nb of days
        $diff = $d1->diff($d2)->format('%r%a');
        if($unit == 'Y'){
            $res = $d / 365.25;
        }
        else if($unit == 'M'){
            $res = $diff / 12;
        }
        else if($unit == 'D'){
            $res = $diff;
        }
        else{
            throw new \Exception("tiglib\\time::diff() : Invalid parameter unit: '$unit'");
        }
        return round($res, $precision);
    }
    
} // end class
