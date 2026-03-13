<?php
/******************************************************************************
    
    @license    GPL
    @history    2026-02-21 22:46:47+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tiglib\time;

class daysOfYear {
    
    /**
        Computes an array of days of a given year, formatted YYYY-MM-DD or MM-DD.
        @param  $year   ex: 1985
        @param  $YYYYMMDD   if true, the result is formatted YYYY-MM-DD
                            if false, the result is formatted MM-DD
        @return     ex: ['1985-01-01' ... '1985-12-31']
    **/
    public static function compute(string $year, bool $YYYYMMDD=true): array {
        $res = [];
        $start = new \DateTime("$year-01-01");
        $end   = (new \DateTime("$year-12-31"))->modify('+1 day');
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
        foreach ($period as $date) {
            if($YYYYMMDD){
                $res[] = $date->format('Y-m-d');
            }
            else{
                $res[] = $date->format('m-d');
            }
        }
        return $res;
    }
    
} // end class
