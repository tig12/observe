<?php
/******************************************************************************
    Adds two distributions sharing the same structure
    
    @license    GPL
    @history    2026-03-23 13:23:02+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\model\distrib;

class AddDistribs {
    
    /**
        Adds 2 distributions of a study.
        @param  $d1 and $d2 must have the same structure.
                ex: [
                    'SO-SO' => [0 => 1230, ... 359 => 1342],
                    ...
                    'NN-NN' => [0 => 1158, ... 359 => 1356]
                ]
        @return Distribution containing the sum of $d1 and $d2
    **/
    public static function add(array &$d1, array &$d2, array &$studyConfig): array {
        $res = [];
        $nDates = count($studyConfig['dates']);
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = $studyConfig['dates'][$i]; // ex: birth
            $res[$dateName] = [];
            // aspects and planets
            foreach(['aspects', 'planets'] as $distribType){
                $res[$dateName][$distribType] = [];
                foreach($d1[$dateName][$distribType] as $distribName => $distribValues){
                    $res[$dateName][$distribType][$distribName] = $distribValues;
                    foreach($d2[$dateName][$distribType][$distribName] as $k => $v){
                        $res[$dateName][$distribType][$distribName][$k] += $v;
                    }
                }
            }
            // day
            $res[$dateName]['day'] = $d1[$dateName]['day'];
            foreach($d2[$dateName]['day'] as $k => $v){
                $res[$dateName]['day'][$k] += $v;
            }
            // year
            $res[$dateName]['year'] = $d1[$dateName]['year'];
            foreach($d2[$dateName]['year'] as $k => $v){
                if(!isset($res[$dateName]['year'][$k])){
                    $res[$dateName]['year'][$k] = $v;
                }
                else{
                    $res[$dateName]['year'][$k] += $v;
                }
            }
            ksort($res[$dateName]['year']);
        }
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // ex: birth-death
                // interaspects
                foreach($d1[$dateName]['interaspects'] as $distribName => $distribValues){
                    $res[$dateName]['interaspects'][$distribName] = $d1[$dateName]['interaspects'][$distribName];
                    foreach($d2[$dateName]['interaspects'][$distribName] as $k => $v){
                        $res[$dateName]['interaspects'][$distribName][$k] += $v;
                    }
                }
                // age
                $res[$dateName]['age'] = $d1[$dateName]['age'];
                foreach($d2[$dateName]['age'] as $k => $v){
                    if(!isset($res[$dateName]['age'][$k])){
                        $res[$dateName]['age'][$k] = $d2[$dateName]['age'][$k];
                    }
                    else{
                        $res[$dateName]['age'][$k] += $d2[$dateName]['age'][$k];
                    }
                }
                ksort($res[$dateName]['age']);
            } // end loop on $j
        } // end loop on $i
        return $res;
    }
    
} // end class
