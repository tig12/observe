<?php
/******************************************************************************
    Computes a number modulo 360.
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2021-02-28 10:18:57+01:00, Thierry Graff : Creation from old java code
    @history    2026-02-25 09:11:38+01:00, Thierry Graff : Convert to a shortcut to modN
    
********************************************************************************/

namespace tiglib\math;

class mod360 {
    
    /**
        Returns a number modulo 360 (between 0 and 360).
        $nb can be integer or decimal, positive or negative
    **/
    public static function compute(float $nb){
        return modN::compute($nb, 360);
    }

} // end class
