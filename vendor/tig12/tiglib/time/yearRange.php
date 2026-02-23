<?php
/******************************************************************************

    @license    GPL
    @history    2026-02-21 22:40:54+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tiglib\time;

class yearRange{
    
    /**
        Computes an array of YYYY years from a string expressing a year or a range of years.
        @param  $strRange   String like "1857" (single year) or "1933-1945" (range of years)
        
    **/
    public static function compute(string $strRange): array|false {
        $years = [];
        $p_year = '/^\d{4}$/';
        $p_range = '/^\d{4}-\d{4}$/';
        preg_match($p_year, $strRange, $m);
        if(count($m) == 1){
            $years[] = $m[0];
        }
        else {
            preg_match($p_range, $strRange, $m);
            if(count($m) == 1){
                $from = substr($m[0], 0, 4);
                $to = substr($m[0], 5);
                // here, could check:
                // - that $from < $to
                // - that $from >= min(available years)
                // - that $to <= max(available years)
                $years = range($from, $to);
            }
            else {
                return false;
            }
        }
        return $years;
    }
    
    
}// end class
