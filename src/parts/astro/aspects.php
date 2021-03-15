<?php
/******************************************************************************
    Computes the distributions of aspects in the charts of each memeber of a MFCW experience.
    
    @todo       Warning : current code works only if $cols contains all the keys of $data[0].
                Computation of $inEmptyLine should be computed not from $cols but from the keys of $data
                Same for computeDouble()
    
    @license    GPL
    @history    2021-03-09 10:09:46+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\astro;

use tiglib\math\mod360;

class aspects {
    
    /** 
        Computes the aspects within one data set.
        Useless aspects are not computed (eg sun-sun, or sun-moon then not moon-sun).
        
        @param  $data       Array of associative arrays.
                            Each assoc. array must have keys = planet codes (IAA) and values = longitudes.
                            ex : [
                                0 => ['SO' => 302.524, 'MO' => 49.212 ... ],
                                ...
                            ]
        @param  $cols       Must contain the keys of $data for which aspects must be computed.
        @param  $skip       Check If some input lines are empty (contain only empty strings) ?
                            If $skip = true, output lines corresponding to empty input lines will contain only empty strings.
        @param  $precision  Nb of decimal digits of the aspects.
        @return An array of associative arrays.
                Keys of each assoc. array are composed by the planet codes used to form the aspects.
                For ex, SO-MO contains sun - moon aspects etc.
                ex : [
                    0 => ['SO-MO' => 253.312 ... ],
                    ...
                ]
    **/
    public static function computeSingle(
        &$data,
        $cols,
        $skip=false,
        $precision=1,
    ) {
        $res = [];
        $inKeys = array_keys($data[0]); // ex ['SO', 'MO', ...]
        $NinKeys = count($inKeys);
        if($skip !== false){
            $outKeys = []; // ex ['SO-MO', SO-ME', ...]
            for($i=0; $i < $NinKeys; $i++){
                for($j=$i+1; $j < $NinKeys; $j++){
                    $outKeys[] = $inKeys[$i] . '-' . $inKeys[$j];
                }
            }
            $outEmptyLine = array_fill_keys($outKeys, '');
            $inEmptyLine = array_fill_keys($inKeys, '');
        }
        foreach($data as $line){
            if($skip !== false && $line == $inEmptyLine){
                $res[] = $outEmptyLine;
                continue;
            }
            $new = [];
            for($i=0; $i < $NinKeys; $i++){
                for($j=$i+1; $j < $NinKeys; $j++){
                    $new[$inKeys[$i] . '-' . $inKeys[$j]]
                        = round(mod360::compute($line[$inKeys[$i]] - $line[$inKeys[$j]]), $precision);
                }
            }
            $res[] = $new;
        }
        return $res;
    }
    
    /** 
        Computes the aspects between 2 data sets.
        $data1 and $data2 must have the same structure : see parameter $data of {@link computeSingle()}
        $data1 and $data2 must have the same length : aspects are computed between $data1[$i] and $data2[$i].
        Each element must be an assoc. array with keys = planet codes (IAA) and values = longitudes
        @param  $cols1      Must contain the keys of $data1 for which aspects must be computed.
        @param  $cols2      Same as $cols1 for $data2
        @param  $skip       See {@link computeSingle()}
        @param  $precision  See {@link computeSingle()}
    **/
    public static function computeDouble(
        &$data1,
        &$data2,
        $cols1,
        $cols2,
        $skip=false,
        $precision=1,
    ) {
        $res = [];
        $inKeys1 = array_keys($data1[0]); // ex ['SO', 'MO', ...]
        $inKeys2 = array_keys($data2[0]); // ex ['SO', 'MO', ...]
        $NinKeys1 = count($inKeys1);
        $NinKeys2 = count($inKeys2);
        $N1 = count($data1);
        $N2 = count($data2);
        if($N1 != $N2){
            throw new ObserveException('$data1 and $data2 must have the same length');
        }
        if($skip !== false){
            $outKeys = []; // ex ['SO-SO', SO-MO', ...]
            for($i=0; $i < $NinKeys1; $i++){
                for($j=0; $j < $NinKeys2; $j++){
                    $outKeys[] = $inKeys1[$i] . '-' . $inKeys2[$j];
                }
            }
            $outEmptyLine = array_fill_keys($outKeys, '');
            $inEmptyLine1 = array_fill_keys($inKeys1, '');
            $inEmptyLine2 = array_fill_keys($inKeys2, '');
        }
        for($i=0; $i < $N1; $i++){
            $line1 =& $data1[$i];
            $line2 =& $data2[$i];
            if($skip !== false && ($line1 == $inEmptyLine1 || $line2 == $inEmptyLine2) ){
                $res[] = $outEmptyLine;
                continue;
            }
            $new = [];
            for($j=0; $j < $NinKeys1; $j++){
                for($k=0; $k < $NinKeys2; $k++){
                    $new[$inKeys1[$j] . '-' . $inKeys2[$k]]
                        = round(mod360::compute($line1[$inKeys1[$j]] - $line2[$inKeys2[$k]]), $precision);
                }
            }
            $res[] = $new;
        }
        return $res;
    }
    
} // end class
