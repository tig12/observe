<?php
/******************************************************************************
    Adds two distributions sharing the same structure
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-03-23 13:23:02+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\model\distrib;

use observe\model\IStudy;

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
    public static function add(array &$d1, array &$d2, IStudy $study): array {
        $res = [];
        $nDates = count($study->config['dates']);
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = $study->config['dates'][$i]; // ex: birth
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
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // ex: birth-death
                // interaspects
                foreach($d1[$dateName]['interaspects']['dim1'] as $distribName => $distribValues){
                    $res[$dateName]['interaspects']['dim1'][$distribName] = $d1[$dateName]['interaspects']['dim1'][$distribName];
                    foreach($d2[$dateName]['interaspects']['dim1'][$distribName] as $k => $v){
                        $res[$dateName]['interaspects']['dim1'][$distribName][$k] += $v;
                    }
                }
                // age
                $res[$dateName]['age-dim1'] = $d1[$dateName]['age-dim1'];
                foreach($d2[$dateName]['age-dim1'] as $k => $v){
                    if(!isset($res[$dateName]['age-dim1'][$k])){
                        $res[$dateName]['age-dim1'][$k] = $d2[$dateName]['age-dim1'][$k];
                    }
                    else{
                        $res[$dateName]['age-dim1'][$k] += $d2[$dateName]['age-dim1'][$k];
                    }
                }
                ksort($res[$dateName]['age-dim1']);
            } // end loop on $j
        } // end loop on $i
        return $res;
    }
    
} // end class
