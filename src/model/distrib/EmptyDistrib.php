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

class EmptyDistrib {
    
    // ***********************************************************************************
    // 1 - Functions aware of study structure    
    // ***********************************************************************************
    
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
//    public static function emptySingleDistrib(array &$codes, int $N = 3): array {
        $res = [];
        foreach($codes as $code){
            $res[$code] = array_fill(0, $N, 0);
        }
        return $res;
    }
    
    /**
        Initialization adapted to interaspects, full combination of the keys.
        Called square because the array can be represented like that (Z represents the zero values):
            SO MO ME MA
        SO  Z  Z  Z  Z
        MO  Z  Z  Z  Z
        ME  Z  Z  Z  Z
        MA  Z  Z  Z  Z
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
//    public static function emptyDoubleDistrib_square(array &$codes1, array &$codes2, int $N = 3): array {
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
        Called triangle because the array can be represented like that (Z represents the zero values):
            SO MO ME MA
        SO  
        MO  Z
        ME  Z  Z
        MA  Z  Z  Z
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
    public static function emptyDoubleDistrib_triangle(array &$codes1, array &$codes2, int $N = 360): array {
//    public static function emptyDoubleDistrib_triangle(array &$codes1, array &$codes2, int $N = 3): array {
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
