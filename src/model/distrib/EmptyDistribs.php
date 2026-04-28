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

use observe\model\IStudy;
use tiglib\time\daysOfYear;

class EmptyDistribs {
    
    // ***********************************************************************************
    // 1 - Functions aware of study structure    
    // ***********************************************************************************
    
    /**
        Initializes the distributions of a study.
        The knowledge of $studyConfig['date'] permits to deduce the distributions of type distrib1 and distrib2 to initialize.
    **/
    public static function initializeDistributions(IStudy $study): array {
        $res = [];
        $nDates = count($study->config['dates']);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $study->config['dates'][$i];
            $res[$dateName] = self::emptyDistrib1($study);
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName1 = $study->config['dates'][$i];
                $dateName2 = $study->config['dates'][$j];
                $res["$dateName1-$dateName2"] = self::emptyDistrib2($study);
            }
        }
        return $res;
    }
    
    /** 
        Prepares an array containing empty distributions of type 1 (single date).
    **/
    public static function emptyDistrib1(IStudy $study): array {
        return [
            'positions'=> self::emptySingleDistrib_dim1($study->config['planets']),
            'aspects' => [
                'dim1' => self::emptyDoubleDistrib_triangle_dim1($study->config['planets'], $study->config['planets']),
                // dim2 is computed in command dim2
            ],
            'day' => self::emptyDayDistrib(),
            'year' => [],
        ];
    }
    
    /** 
        Prepares an array containing empty distributions of type 2 (relations between two dates).
    **/
    public static function emptyDistrib2(IStudy $study): array {
        return [
            'interaspects' => [
                'dim1' => self::emptyDoubleDistrib_square_dim1($study->config['planets'], $study->config['planets']),
                // dim2 is computed in command dim2
                //'dim2' =>  self::emptyDoubleDistrib_square_dim2($study->config['planets'], $study->config['planets']), 
            ],
            'age-dim1' => [],
        ];
    }
    
    
    // ***********************************************************************************
    // 2 - Generic functions    
    // ***********************************************************************************
    
    
    /**
        Prepares an array of distributions.
            - "single" because the keys of the resulting array are just the $codes.
            - "dim1" because the empty distributions are dim1 arrays.
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
    public static function emptySingleDistrib_dim1(array $codes, int $N = 360): array {
        $res = [];
        foreach($codes as $code){
            $res[$code] = array_fill(0, $N, 0);
        }
        return $res;
    }
    
    /**
        Prepares an array of distributions adapted to aspects (partial combination of the keys).
            - "double" because the keys of the resulting array are a combination of $codes1 and $codes2.
            - "dim1" because the empty distributions are of type dim1.
            - "triangle" because the array can be represented like that (V represents values):
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
    public static function emptyDoubleDistrib_triangle_dim1(array $codes1, array $codes2, int $N = 360): array {
        $res = [];
        $empty = array_fill(0, $N, 0);
        for($i=0; $i < count($codes1); $i++){
            for($j=$i+1; $j < count($codes2); $j++){
                $key = $codes1[$i] . '-' . $codes2[$j];
                $res[$key] = $empty;
            }
        }
        return $res;
    }
    
    /**
        Prepares an array of distributions adapted to aspects (partial combination of the keys).
            - "double" because the keys of the resulting array are a combination of $codes1 and $codes2.
            - "dim2" because the empty distributions are of type dim2.
            - "triangle" because the array can be represented like that (V represents values):
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
                    'SO-MO' => [
                        0   => [0 => 0,  ... 359 => 0],
                        ...
                        359 => [0 => 0,  ... 359 => 0],
                    ],
                    'SO-ME' => [
                        0   => [0 => 0,  ... 359 => 0],
                        ...
                        359 => [0 => 0,  ... 359 => 0],
                    ],
                    ...
                ]
    **/
    public static function emptyDoubleDistrib_triangle_dim2(array $codes1, array $codes2, int $N = 360): array {
        $res = [];
        $empty = array_fill(0, $N, array_fill(0, $N, 0));
        for($i=0; $i < count($codes1); $i++){
            for($j=$i+1; $j < count($codes2); $j++){
                $key = $codes1[$i] . '-' . $codes2[$j];
                $res[$key] = $empty;
            }
        }
        return $res;
    }
    
    /**
        Prepares an array of distributions adapted to interaspects (full combination of the keys).
            - "double" because the keys of the resulting array are a combination of $codes1 and $codes2.
            - "dim1" because the empty distributions are of type dim1.
            - "square" because the array can be represented like that (V represents values):
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
    public static function emptyDoubleDistrib_square_dim1(array $codes1, array $codes2, int $N = 360): array {
        $res = [];
        $empty = array_fill(0, $N, 0);
        foreach($codes1 as $code1){
            foreach($codes2 as $code2){
                $res["$code1-$code2"] = $empty;
            }
        }
        return $res;
    }
    
    /**
        Prepares an array of distributions adapted to interaspects (full combination of the keys).
            - "double" because the keys of the resulting array are a combination of $codes1 and $codes2.
            - "dim2" because the empty distributions are of type dim2.
            - "square" because the array can be represented like that (V represents values):
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
                    'SO-SO' => [
                        0   => [0 => 0,  ... 359 => 0],
                        ...
                        359 => [0 => 0,  ... 359 => 0],
                    ],
                    'SO-MO' => [
                        0   => [0 => 0,  ... 359 => 0],
                        ...
                        359 => [0 => 0,  ... 359 => 0],
                    ],
                    ...
                ]

    **/
    public static function emptyDoubleDistrib_square_dim2(array $codes1, array $codes2, int $N = 360): array {
        $res = [];
        $empty = array_fill(0, $N, array_fill(0, $N, 0));
        foreach($codes1 as $code1){
            foreach($codes2 as $code2){
                $res["$code1-$code2"] = $empty;
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
