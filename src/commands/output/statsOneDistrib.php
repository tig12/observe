<?php
/******************************************************************************
    
    Generates a table contianing statistics about a distribution.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-04-13 11:14:24+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\output;

class statsOneDistrib {
    
    /** 
        @param  $statObs and $statExp
                Associative arrays structured as described in observe\model\distrib\StatsDistrib::STATS_CSV_FIELDS
                (keys are 'MIN' 'MAX' etc.)
    **/
    public static function html(array $statObs, array $statExp): string{
        return output_page::template('stats-one-distrib.html', [
            'obs' => $statObs,
            'exp' => $statExp,
        ]);
    }
    
}// end class
