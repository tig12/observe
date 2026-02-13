<?php
/******************************************************************************
    Utilities related to time computation.
    
    @license    GPL
    @history    2026-02-13 21:32:14+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\astro;

class time {
    
    /**
        Returns a list of days YYYY-MM-DD of a given year.
        @param  $year   ex: 1985
    **/
    public static function listDays(string $year): array {
        $res = [];
        $start = new \DateTime("$year-01-01");
        $end   = (new \DateTime("$year-12-31"))->modify('+1 day');
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
        foreach ($period as $date) {
            $res[] = $date->format('Y-m-d');
        }
        return $res;
    }
    
    /**
        Computes an array of YYYY years from a string expressing a year or a range of years.
        @param  $strRange   String like "1857" (single year) or "1933-1945" (range of years)
        
    **/
    public static function yearRange(string $strRange): array|false {
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
                // here, should check:
                // - that $from < $to
                // - that $from >= min(available years)
                // - that $to >= max(available years)
                // - that all dates between $from and $to correspond to existing dates
                // not done because it's a build command, executed by a person supposed to be careful
                $years = range($from, $to);
            }
            else {
                return false;
            }
        }
        return $years;
    }
    
}// end class
