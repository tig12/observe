<?php
/****************************************************************************************

    Basic indicators of a distribution

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-04-09 22:36:58+01:00, Thierry Graff : Add to tiglib
    @history    2021-03-14 17:59:59+01:00, Thierry Graff : Creation from existing code
****************************************************************************************/

namespace tiglib\stats;

class distrib {
    
    //
    // Center of a distribution
    //
    
    /**
        Arithmetic mean of $data.
        @param  $data Regular or associative array. Values are used to compute the mean
    **/
    public static function mean(array &$data): float {
        return array_sum($data) / count($data);
    }
    
    /**
        Mode of a distribution = the most represented value.
        @param  $data Regular or associative array.
    **/
    public static function mode(array &$data): float {
        return 999; // TODO implement
    }
    
    /**
        Median of a distribution: split the distribution in 2 equal parts.
        @param  $data Regular or associative array.
    **/
    public static function median(array &$data): float {
        return 999; // TODO implement
    }
    
    //
    // Dispersion of a distribution
    //
    
    /**
        Ecart type - Standard deviation.
        @param  $data Regular or associative array. Values are used to compute the mean.
    **/
    public static function sigma(array &$data): float {
        $mean = self::mean($data);
        $n = count($data);
        $values = array_values($data);
        $res = 0;
        for($i=0; $i < $n; $i++){
            $res += pow(($values[$i] - $mean), 2);
        }
        $res = sqrt($res / ($n - 1));
        return $res;
    }
    
    /**
        Etendue = max - min = span in english ?
        @param  $data Regular or associative array.
    **/
    public static function etendue(array &$data): float {
        return 999; // TODO implement
    }
    
} // end class
