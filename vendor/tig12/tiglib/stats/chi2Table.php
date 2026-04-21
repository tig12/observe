<?php
/****************************************************************************************
    
    Computes the statistics of a chi2 table.
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-04-13 21:03:51+01:00, Thierry Graff : Creation
****************************************************************************************/

namespace tiglib\stats;

class chi2Table {

    /**
        @param  $data   2-dim array of M rows and N columns
            [
                0 =>    [0 ... N-1]
                ...
                M-1 =>  [0 ... N-1]
            ]
        @param  $scale  Number of categories to compute the normalized deviations.
                        Range of deviations goes from -$scale to +$scale.
    **/
    public static function compute(array &$a, $scale = 4, bool $test=false): array {
        $M = count($a);     // nb of rows       - loop on $j
        $N = count($a[0]);  // nb of columns    - loop on $i
        //
        $sum = 0; // sum of all elements of the array
        $sums_j = array_fill(0, $M, 0); // sums of each line
        $sums_i = array_fill(0, $N, 0); // sums of each column
        //
        for($j=0; $j < $M; $j++){ // loop on rows
            for($i=0; $i < $N; $i++){ //loop on columns
                $sum += $a[$j][$i];
                $sums_i[$i] += $a[$j][$i];
                $sums_j[$j] += $a[$j][$i];
            }
        }
        //
        $theo = []; // theoretical array
        $c2 = [];   // individual contributions to chi2
        $diff = []; // individual deviations from theoretical values
        $diff_percent = []; // percentage of individual deviations from theoretical values
        $diff_min = $diff_max = 0;  ////////////// useless ???
        $chi2 = 0;  // global chi2
        for($j=0; $j < $M; $j++){ // loop on rows
            for($i=0; $i < $N; $i++){ //loop on columns
                $theo[$j][$i] = $sums_i[$i] * $sums_j[$j] / $sum;
                $diff[$j][$i] = $a[$j][$i] - $theo[$j][$i];
                if($theo[$j][$i] != 0){
                    $diff_percent[$j][$i] = 100 * $diff[$j][$i] / $theo[$j][$i];
                    $c2[$j][$i] = pow($diff[$j][$i], 2) / $theo[$j][$i];
                }
                else{
                    // TODO check if this is correct 
                    $diff_percent[$j][$i] = 0;
                    $c2[$j][$i] = 0;
                }
                $chi2 += $c2[$j][$i];
                if($diff[$j][$i] > $diff_max){
                    $diff_max = $diff[$j][$i];
                }
                if($diff[$j][$i] < $diff_min){
                    $diff_min = $diff[$j][$i];
                }
            }
        }
        //
        $round = 2;
        for($j=0; $j < $M; $j++){ // loop on rows
            for($i=0; $i < $N; $i++){ //loop on columns
                $diff[$j][$i] = round($diff[$j][$i], $round);
            }
            $diff_min = round($diff_min, $round);
            $diff_max = round($diff_max, $round);
        }
        
        if($test){
            return [
                'diff' => $diff,
                'diff_percent' => $diff_percent,
                'sum' => $sum,
                'sums_i' => $sums_i,
                'sums_j' => $sums_j,
                'theo' => $theo,
                'c2' => $c2,
                'chi2' => $chi2,
            ];
        }
        return [
            'diff' => $diff,
            'diff_max' => $diff_max,
            'diff_min' => $diff_min,
            'chi2' => $chi2,
        ];
    }
    
}// end class
