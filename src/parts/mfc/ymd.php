<?php
/******************************************************************************
    Computes year, day, age distributions from a csv file containing YYYY-MM-DD dates
    
    @license    GPL
    @history    2021-02-14 11:05:05+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\mfc;

use observe\app\Observe;
use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;
use tiglib\time\diff;

class ymd {
                                                                               
    // ******************************************************
    /**
        @param $data    Array representing a csv file.
                        Each entry is an associative array representing one line of the file
        @param $columns Name of the columns representing M, F, C, W, CRANK in the csv file
        @param $skip    
    **/
    public static function loadYMD(
        &$data,
        $columns,
        $skipW,
    ){
        
        $dist = [
            'M' => [
                'year' => [],
                'day' => [],
                'age-wed' => [],
                'age-child' => [],
            ],
            'F' => [
                'year' => [],
                'day' => [],
                'age-wed' => [],
                'age-child' => [],
            ],
            'C' => [
                'year' => [],
                'day' => [],
                'rank' => [],
                'wed-birth' => [], // interval [wedding - child birth]
            ],
            'W' => [
                'year' => [],
                'delta-mf' => [], // interval [father birth - mother birth] // TODO 
                'N' => 0, // nb of rows with wedding info
            ],
        ];
        
        $colM = $columns['M'];
        $colF = $columns['F'];
        $colC = $columns['C'];
        $colW = $columns['W'];
        $colCRANK = $columns['CRANK']; // child rank
        
        $nW = 0;
        $n = 0;
        
        $lineHasWedding = function($line) use ($columns, $skipW) {
            return $line[$columns['W']] != $skipW;
        };
        
        foreach($data as $line){
            $n++; 
            [$yM, $mM, $dM] = explode('-', $line[$colM]);
            [$yF, $mF, $dF] = explode('-', $line[$colF]);
            [$yC, $mC, $dC] = explode('-', $line[$colC]);
            $dateM = date_create($line[$colM]);
            $dateF = date_create($line[$colF]);
            $dateC = date_create($line[$colC]);
            // $doy = $dateM->format('z');
            if($lineHasWedding($line)){
                $nW++;                                                                      
                [$yW, $mW, $dW] = explode('-', $line[$colW]);
                $dateW = date_create($line[$colW]);
            }
            //
            // M
            //
            // year
            if(!isset($dist['M']['year'][$yM])){ $dist['M']['year'][$yM] = 0; }
            $dist['M']['year'][$yM]++;
            // day MM-DD
            $doy = "$mM-$dM"; // day of year MM-DD
            if(!isset($dist['M']['day'][$doy])){ $dist['M']['day'][$doy] = 0; }
            $dist['M']['day'][$doy]++;
            // age at wedding
            if($lineHasWedding($line)){
                $age = diff::compute($dateM, $dateW);
                if(!isset($dist['M']['age-wed'][$age])){ $dist['M']['age-wed'][$age] = 0; }
                $dist['M']['age-wed'][$age]++;
            }
            // age at child birth
            $age = diff::compute($dateM, $dateC);
            if(!isset($dist['M']['age-child'][$age])){ $dist['M']['age-child'][$age] = 0; }
            $dist['M']['age-child'][$age]++;
            //
            // F
            //
            // year
            if(!isset($dist['F']['year'][$yF])){ $dist['F']['year'][$yF] = 0; }
            $dist['F']['year'][$yF]++;
            // day MM-DD
            $doy = "$mF-$dF"; // day of year MM-DD
            if(!isset($dist['F']['day'][$doy])){ $dist['F']['day'][$doy] = 0; }
            $dist['F']['day'][$doy]++;
            // age at wedding
            if($lineHasWedding($line)){
                $age = diff::compute($dateF, $dateW);
                if(!isset($dist['F']['age-wed'][$age])){ $dist['F']['age-wed'][$age] = 0; }
                $dist['F']['age-wed'][$age]++;
            }
            // age at child birth
            $age = diff::compute($dateF, $dateC);
            if(!isset($dist['F']['age-child'][$age])){ $dist['F']['age-child'][$age] = 0; }
            $dist['F']['age-child'][$age]++;
            //
            // C
            //
            // year
            if(!isset($dist['C']['year'][$yC])){ $dist['C']['year'][$yC] = 0; }
            $dist['C']['year'][$yC]++;
            // day MM-DD
            $doy = "$mC-$dC"; // day of year MM-DD
            if(!isset($dist['C']['day'][$doy])){ $dist['C']['day'][$doy] = 0; }
            $dist['C']['day'][$doy]++;
            // child rank
            $rank = $line[$colCRANK];
            if(!isset($dist['C']['rank'][$rank])){ $dist['C']['rank'][$rank] = 0; }
            $dist['C']['rank'][$rank]++;
            // interval wedding - birth
            if($lineHasWedding($line)){
                $diff = diff::compute($dateW, $dateC, unit:'M');
                if(!isset($dist['C']['wed-birth'][$diff])){ $dist['C']['wed-birth'][$diff] = 0; }
                $dist['C']['wed-birth'][$diff]++;
            }
            //
            // W
            //
            if($lineHasWedding($line)){
                // N
                $dist['W']['N']++;
                // year
                if(!isset($dist['W']['year'][$yW])){ $dist['W']['year'][$yW] = 0; }
                $dist['W']['year'][$yW]++;
                // day MM-DD
                $doy = "$mW-$dW"; // day of year MM-DD
                if(!isset($dist['W']['day'][$doy])){ $dist['W']['day'][$doy] = 0; }
                $dist['W']['day'][$doy]++;
            }
// echo "$yF-$mF-$dF\n";
// echo "$yW-$mW-$dW\n";
// echo "$yC-$mC-$dC\n";
//echo "diff = $diff\n";
        }
        ksort($dist['M']['year']);
        ksort($dist['M']['day']);
        ksort($dist['M']['age-wed']);
        ksort($dist['M']['age-child']);
        ksort($dist['F']['year']);
        ksort($dist['F']['day']);
        ksort($dist['F']['age-wed']);
        ksort($dist['F']['age-child']);
        ksort($dist['C']['year']);
        ksort($dist['C']['day']);
        ksort($dist['C']['rank']);
        ksort($dist['C']['wed-birth']);
        ksort($dist['W']['year']);
        ksort($dist['W']['day']);
        return $dist;
    }
    
} // end class
