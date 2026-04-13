<?php
/******************************************************************************
    
    A legend is an array of couples (x value, label), to add labels on x axis when drawing distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-04-11 19:19:45+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\model\draw;

class xlegend {
    
    /**
        @param  $
    **/
    public static function angle360(): array {
        $res = [];
        foreach([0, 90, 180, 270] as $x){
            $res[$x] = $x;
        }
        $res[359] = 360; // cheat
        return $res;
    }
    
    public static function angle12(): array {
        $res = [];
        foreach([0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330] as $x){
            $res[$x] = $x;
        }
        return $res;
    }
    
    public static function month(bool $trimZero = false): array {
        $res = [];
        foreach(['01-01', '02-01', '03-01', '04-01', '05-01', '06-01', '07-01', '08-01', '09-01', '10-01', '11-01', '12-01'] as $x){
            $res[$x] = substr($x, 0, 2);
            if($trimZero){
                $res[$x] = ltrim($res[$x], '0');
            }
        }
        return $res;
    }
    
}// end class
