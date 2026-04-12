<?php
/******************************************************************************
    
    Computes indicators of a distribution related to min and max.
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-04-09 22:41:12+01:00, Thierry Graff : Add to tiglib
    @history    2021-03-14 17:59:59+01:00, Thierry Graff : Creation from existing code
********************************************************************************/

namespace tiglib\stats;

class minMax {
    
    /**
        Returns an array with 6 elements:
            from        Min value of the keys
            to          Max value of the keys
            min_key     Key corresponding to min
            min         Min value of the values
            max_key     Key corresponding to max
            max         Max value of the values
    **/
    public static function minMaxIndicators(&$data): array {
        $from = self::from($data);
        $to = self::to($data);
        [$min_key, $min, $idx] = self::min_key($data);
        [$max_key, $max, $idx] = self::max_key($data);
        return [$from, $to, $min_key, $min, $max_key, $max];
    }
    
    /**
        Computes the "min key" = the key of $data corresponding to the lowest value
        (if several keys correspond to the same lowest value, the first key is returned).
        @param  $data Associative array.
        @return Array with 3 elements :
            - the key corresponding to the lowest value,
            - the lowest value,
            - the place of this key in the array (0 = first key of the array...).
            Ex: $data = ['toto' => 3, 'titi' => 5, 'tata' => 5, 'tutu' => 4]
                returns ['toto', 3, 0]
    **/
    public static function min_key(&$data): array {
        $min = min($data);
        $key = '';
        $index = 0;
        foreach($data as $k => $v){
            if($v == $min){
                $key = $k;
                break;
            }
            $index++;
        }
        return [$key, $min, $index];
    }
    
    /**
        Computes the "max key" = the key of $data corresponding to the highest value
        (if several keys correspond to the same highest value, the first key is returned).
        @param  $data Associative array.
        @return Array with 3 elements :
            - the key corresponding to the highest value,
            - the highest value,
            - the place of this key in the array (0 = first key of the array...).
            Ex: $data = ['toto' => 3, 'titi' => 5, 'tata' => 5, 'tutu' => 4]
                returns ['titi', 1]
    **/
    public static function max_key(&$data): array {
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
        return [$key, $max, $index];
    }
    
    /**
        @param  $
    **/
    public static function from(&$data) {
        return min(array_keys($data)); // min() also compares correctly non-numeric keys
    }
    
    public static function to(&$data) {
        return max(array_keys($data)); // max() also compares correctly non-numeric keys
    }
    
} // end class
