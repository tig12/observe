<?php
/******************************************************************************
    Main class to compute distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-13 18:44:21+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\model\distrib;

use observe\model\Observe;

class Distribs {
    
    /** 
        Conductor of distribution omputation.
        @param  $func Function which yields the data whose distributions need to be computed.
    **/
    public static function computeDistribs(callable $func, array $studyConfig) {
        $res = self::initializeResult($line, $studyConfig);
        foreach($func() as $line){
            $line = trim($line);
        }
    }
    
    /**
        Initializes the distributions of a study.
        The knowledge of $studyConfig['date'] permits to deduce the distributions of type distrib1 and distrib2 to initialize.
    **/
    private static function initializeDistribs(array &$studyConfig): array {
        $res = [];
        $n = count($studyConfig['dates']);
        // distributions of type distrib1
        for($i=0; $i < $n; $i++){
            $name = $studyConfig['dates'][$i];
            $res[$name] = EmptyDistrib::emptyDistrib1($studyConfig);
        }
        // distributions of type distrib2
        for($i=0; $i < $n; $i++){
            for($j=$i+1; $j < $n; $j++){
                $name1 = $studyConfig['dates'][$i];
                $name2 = $studyConfig['dates'][$j];
                $res["$name1-$name2"] = EmptyDistrib::emptyDistrib2($studyConfig);
            }
        }
        return $res;
    }
    
} // end class
