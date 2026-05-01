<?php
/******************************************************************************
    Adds two distributions sharing the same structure
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
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
        @param  $dateNames      ex: ['birth', 'death']
        @return Distribution containing the sum of $d1 and $d2, done element by element.
    **/
    public static function add(array &$d1, array &$d2, array $dateNames): array {
        $res = [];
        $nDates = count($dateNames);
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = $dateNames[$i]; // ex: birth
            $res[$dateName] = [];
            // planet positions
            $res[$dateName]['positions'] = [];
            foreach($d1[$dateName]['positions'] as $distribName => $distribValues){
                $res[$dateName]['positions'][$distribName] = $distribValues;
                foreach($d2[$dateName]['positions'][$distribName] as $k => $v){
                    $res[$dateName]['positions'][$distribName][$k] += $v;
                }
            }
            // aspects
            $res[$dateName]['aspects'] = [];
            foreach($d1[$dateName]['aspects']['dim1'] as $distribName => $distribValues){
                $res[$dateName]['aspects']['dim1'][$distribName] = $distribValues;
                foreach($d2[$dateName]['aspects']['dim1'][$distribName] as $k => $v){
                    $res[$dateName]['aspects']['dim1'][$distribName][$k] += $v;
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
                $dateName = $dateNames[$i] . '-' . $dateNames[$j]; // ex: birth-death
                // interaspects
                foreach($d1[$dateName]['interaspects']['dim1'] as $distribName => $distribValues){
                    $res[$dateName]['interaspects']['dim1'][$distribName] = $d1[$dateName]['interaspects']['dim1'][$distribName];
                    foreach($d2[$dateName]['interaspects']['dim1'][$distribName] as $k => $v){
                        $res[$dateName]['interaspects']['dim1'][$distribName][$k] += $v;
                    }
                }
                // age
//                foreach(['D', 'M', 'Y'] as $unit){
                foreach(['M', 'Y'] as $unit){
                    $res[$dateName]['age']['dim1']["age-$unit"] = $d1[$dateName]['age']['dim1']["age-$unit"];
                    foreach($d2[$dateName]['age']['dim1']["age-$unit"] as $k => $v){ // $k = age
                        if(!isset($res[$dateName]['age']['dim1']["age-$unit"][$k])){
                            $res[$dateName]['age']['dim1']["age-$unit"][$k] = $d2[$dateName]['age']['dim1']["age-$unit"][$k];
                        }
                        else{
                            $res[$dateName]['age']['dim1']["age-$unit"][$k] += $d2[$dateName]['age']['dim1']["age-$unit"][$k];
                        }
                    }
                    ksort($res[$dateName]['age']['dim1']["age-$unit"]);
                }
            } // end loop on $j
        } // end loop on $i
        return $res;
    }
    
} // end class
