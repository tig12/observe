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
            $i loops on rows [0, M-1]
            $j loops on cols [0, N-1]
            $a[$i][$j] = row $i, col $j
    **/
    public static function compute(array &$a, int $round = 2, bool $test=false): array {
        $M = count($a);     // nb of rows       - loop on $i
        $N = count($a[0]);  // nb of columns    - loop on $j
        //
        $sum = 0; // sum of all elements of the array
        $sums_i = array_fill(0, $M, 0); // sums of each row
        $sums_j = array_fill(0, $N, 0); // sums of each col
        //
        for($i=0; $i < $M; $i++){ // loop on rows
            for($j=0; $j < $N; $j++){ // loop on cols
                $sum += $a[$i][$j];
                $sums_i[$i] += $a[$i][$j];
                $sums_j[$j] += $a[$i][$j];
            }
        }
        //
        $theo = []; // theoretical array
        $c2 = [];   // individual contributions to chi2
        $diff = []; // individual deviations from theoretical values
        $diff_percent = []; // percentage of individual deviations from theoretical values
        $chi2 = 0;  // global chi2
        for($i=0; $i < $M; $i++){ // loop on rows
            for($j=0; $j < $N; $j++){ // loop on cols
                $theo[$i][$j] = $sums_i[$i] * $sums_j[$j] / $sum;
                $diff[$i][$j] = $a[$i][$j] - $theo[$i][$j];
                if($theo[$i][$j] != 0){
                    $diff_percent[$i][$j] = 100 * $diff[$i][$j] / $theo[$i][$j];
                    $c2[$i][$j] = pow($diff[$i][$j], 2) / $theo[$i][$j];
                }
                else{
                    // TODO check if this is correct 
                    $diff_percent[$i][$j] = 0;
                    $c2[$i][$j] = 0;
                }
                $chi2 += $c2[$i][$j];
            }
        }
        //
        for($i=0; $i < $M; $i++){ // loop on rows
            for($j=0; $j < $N; $j++){ //loop on cols
                $diff[$i][$j] = round($diff[$i][$j], $round);
            }
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
            'diff_percent' => $diff_percent,
            'chi2' => $chi2,
        ];
    }
    
}// end class
