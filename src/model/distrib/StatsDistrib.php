<?php
/******************************************************************************
    
    Loads statistical informations of distributions from file stats.csv.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-04-10 11:53:03+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model\distrib;

use observe\model\Observe;
use observe\model\Studies;
use tiglib\arrays\csvAssociative;

class StatsDistrib {
    
    /**
        @param  $
    **/
    public static function loadStats(array &$studyConfig, string $split, string $subgroup): array {
        $res = [];
        $filename = Studies::getSubgroupDirectory($studyConfig, $split, $subgroup) . DS . 'stats.csv';
        $lines = csvAssociative::compute($filename, Observe::CSV_SEP);
        foreach($lines as $line){
            if(!isset($res[$line['DATE_NAME']])){
                $res[$line['DATE_NAME']] = [];
            }
            if(!isset($res[$line['DATE_NAME']][$line['DISTRIB_TYPE']])){
                $res[$line['DATE_NAME']][$line['DISTRIB_TYPE']] = [];
            }
            $line2 = $line;
            unset($line2['DATE_NAME']);
            unset($line2['DISTRIB_TYPE']);
            unset($line2['DISTRIB']);
            if($line['DISTRIB'] == ''){
                // day, year, age
                $res[$line['DATE_NAME']][$line['DISTRIB_TYPE']] = $line2;
            }
            else{
                // planets, aspects, interaspects
                $res[$line['DATE_NAME']][$line['DISTRIB_TYPE']][$line['DISTRIB']] = $line2;
            }
        }
        return $res;
    }
    
} // end class
