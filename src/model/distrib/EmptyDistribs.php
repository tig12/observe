<?php
/******************************************************************************
    
    Initializes empty distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-03-12 16:07:07+01:00, Thierry Graff : Isolate this code in a separated class
    @history    2021-03-14 20:15:29+01:00, Thierry Graff : Big refactor
    @history    2021-03-10 04:31:53+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\model\distrib;

use tiglib\time\daysOfYear;

class EmptyDistribs {
    
    // ***********************************************************************************
    // 1 - Functions aware of study structure    
    // ***********************************************************************************
    
    /**
        Initializes the distributions of a study.
        The knowledge of $studyConfig['date'] permits to deduce the distributions of type distrib1 and distrib2 to initialize.
    **/
    public static function initializeDistributions(array &$studyConfig): array {
        $res = [];
        $nDates = count($studyConfig['dates']);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $studyConfig['dates'][$i];
            $res[$dateName] = self::emptyDistrib1($studyConfig);
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName1 = $studyConfig['dates'][$i];
                $dateName2 = $studyConfig['dates'][$j];
                $res["$dateName1-$dateName2"] = self::emptyDistrib2($studyConfig);
            }
        }
        return $res;
    }
    
    /** 
        Prepares an array containing empty distributions of type 1 (single date).
    **/
    public static function emptyDistrib1(array &$studyConfig): array {
        return [
            'planets'=> self::emptySingleDistrib($studyConfig['planets']),
            'aspects' => self::emptyDoubleDistrib_triangle($studyConfig['planets'], $studyConfig['planets']),
            'day' => self::emptyDayDistrib(),
            'year' => [],
        ];
    }
    
    /** 
        Prepares an array containing empty distributions of type 2 (relations between two dates).
    **/
    public static function emptyDistrib2(array &$studyConfig): array {
        return [
            'interaspects' => self::emptyDoubleDistrib_square($studyConfig['planets'], $studyConfig['planets']),
            'age' => [],
        ];
    }
    
    
    // ***********************************************************************************
    // 2 - Generic functions    
    // ***********************************************************************************
    
    /**
        @param  $codes  Array containing keys
                ex: ['SO', 'MO', 'ME' ...]
        @param  $N The number of elements in the values of the result.
        @return Associative array   keys = $codes
                                    values = array with $N values initialzed to 0.
                ex: [
                    'SO' => [0 => 0, ... 359 => 0],
                    ...
                    'NN' => [0 => 0, ... 359 => 0]
                ]
    **/
    public static function emptySingleDistrib(array &$codes, int $N = 360): array {
        $res = [];
        foreach($codes as $code){
            $res[$code] = array_fill(0, $N, 0);
        }
        return $res;
    }
    
    /**
        Initialization adapted to interaspects, full combination of the keys.
        Called square because the array can be represented like that (V represents values):
            SO MO ME MA
        SO  V  V  V  V
        MO  V  V  V  V
        ME  V  V  V  V
        MA  V  V  V  V
        @param  $codes1 and $codes2     Arrays containing keys
                ex: ['SO', 'MO', 'ME' ...]
        @param  $N The number of elements in the values of the result.
        @return Associative array   keys = combination of $codes1 and $codes2
                                    values = array with $N values initialzed to 0.
                ex: [
                    'SO-SO' => [0 => 0, ... 359 => 0],
                    ...
                    'NN-NN' => [0 => 0, ... 359 => 0]
                ]
    **/
    public static function emptyDoubleDistrib_square(array &$codes1, array &$codes2, int $N = 360): array {
        $res = [];
        foreach($codes1 as $code1){
            foreach($codes2 as $code2){
                $res["$code1-$code2"] = array_fill(0, $N, 0);
            }
        }
        return $res;
    }
    
    /**
        Initialization adapted to aspects, partial combination of the keys.
        Called triangle because the array can be represented like that (V represents values):
            SO MO ME MA
        SO  
        MO  V
        ME  V  V
        MA  V  V  V
        @param  $codes1 and $codes2     Arrays containing keys
                ex: ['SO', 'MO', 'ME' ...]
        @param  $N The number of elements in the values of the result.
        @return Associative array   keys = combination of $codes1 and $codes2
                                    values = array with $N values initialzed to 0.
                ex: [
                    'SO-MO' => [0 => 0, ... 359 => 0],
                    ...
                    'PL-NN' => [0 => 0, ... 359 => 0]
                ]
    **/
    public static function emptyDoubleDistrib_triangle(array &$codes1, array &$codes2, int $N = 360): array {
        $res = [];
        for($i=0; $i < count($codes1); $i++){
            for($j=$i+1; $j < count($codes2); $j++){
                $key = $codes1[$i] . '-' . $codes2[$j];
                $res[$key] = array_fill(0, $N, 0);
            }
        }
        return $res;
    }
    
    /**
        @return Associative array   keys = days in format MM-DD
                                    values = array with $N values initialzed to 0.
                [
                    '01-01' => 0,
                    '01-02' => 0,
                    ...
                    '02-29' => 0
                    ...
                    '12-31' => 0
                ]
    **/
    public static function emptyDayDistrib(): array {
        $res = [];
        $days = daysOfYear::compute(2004, false); // 2004 because bissextile year is needed
        foreach($days as $day){
            $res[$day] = 0;
        }
        return $res;
    }
    
} // end class
