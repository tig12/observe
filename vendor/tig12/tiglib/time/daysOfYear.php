<?php
/******************************************************************************
    
    @license    GPL
    @history    2026-02-21 22:46:47+01:00, Thierry Graff : Creation
********************************************************************************/
namespace tiglib\time;

class daysOfYear {
    
    /**
        Computes an array of days YYYY-MM-DD of a given year.
        @param  $year   ex: 1985
        @return         ex: ['1985-01-01' ... '1985-12-31']
    **/
    public static function compute(string $year): array {
        $res = [];
        $start = new \DateTime("$year-01-01");
        $end   = (new \DateTime("$year-12-31"))->modify('+1 day');
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
        foreach ($period as $date) {
            $res[] = $date->format('Y-m-d');
        }
        return $res;
    }
    
} // end class
