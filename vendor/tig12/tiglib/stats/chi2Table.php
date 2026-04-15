<?php
/****************************************************************************************

    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2026-04-13 21:03:51+01:00, Thierry Graff : Creation
****************************************************************************************/

namespace tiglib\stats;

class chi2Table {

    /**
        @param  $data   2-dim array [
            0 =>    [0 ... N-1]
            ...
            M-1 =>  [0 ... N-1]
        ]        
    **/
    public static function compute(array &$a, bool $test=false): array {
        $N = count($a[0]); // nb of columns
        $M = count($a); // nb of lines
        //
        $sum = 0; // sum of all elements of the array
        $sums_i = array_fill(0, $M, 0); // sums of columns
        $sums_j = array_fill(0, $N, 0); // sums of lines
        for($i=0; $i < $N; $i++){
            for($j=0; $j < $M; $j++){
                $sum += $a[$i][$j];
                $sums_i[$i] += $a[$i][$j];
                $sums_j[$j] += $a[$i][$j];
            }
        }
        //
        $theo = []; // theoretical array
        $c2 = []; // individual contributions to chi2
        $chi2 = 0; //global chi2
        for($i=0; $i < $N; $i++){
            for($j=0; $j < $M; $j++){
                $theo[$i][$j] = $a[$i][$j] * $sums_i[$i] * $sums_j[$j] / $sum;
                $c2[$i][$j] = pow($theo[$i][$j] - $a[$i][$j], 2) / $theo[$i][$j];
                $chi2 += $c2[$i][$j];
            }
        }
        if($test){
            return [
                $c2,
                $chi2,
                $sums_i,
                $sums_j,
            ];
        }
        return [
            $c2,
            $chi2,
        ];
    }
    
}// end class
