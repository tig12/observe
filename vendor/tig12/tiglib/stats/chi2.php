<?php
/****************************************************************************************
    Computations related to chi square test.
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2016-11-27 23:23:59+01:00, Thierry Graff : Creation for jetheme
    @history    2026-02-26 22:16:02+01:00, Thierry Graff : Inclusion in tiglib
****************************************************************************************/

namespace tiglib\stats;

class chi2 {

    /**
        @param  $
    **/
    public static function chi2AndProba(int $df, array $O, array $E) {
        $chi2 = self::chi2($O, $E);
        return [
            $chi2,
            self::chi2Proba($chi2, $df),
        ];
    }
    
    /**
        Computes the chi square value to compare observed and expected values,
        for contingency table with only one column.
        @param  $O  regular array containing observed values
        @param  $E  regular array containing expected values
    **/
    public static function chi2(array $O, array $E): int|float{
        $res = 0;
        $N = count($O);
        for($i=0; $i < $N; $i++){
            if($E[$i] != 0){
                $res += pow($O[$i] - $E[$i], 2) / $E[$i];
            }
        }
        return $res;
    }
    
    /**
        Computes the probability to get a given chi square distribution.
        Port of javascript function "ChiSq", from John Pezzullo, found at
        http://members.aol.com/johnp71/pdfs.html
        @param $x The chi square distribution
        @param $n The degrees of freedom
    **/
    public static function chi2Proba($x, $n){
        if($n == 1 && $x > 1000){
            return 0;
        }
        if($x>1000 | $n>1000){
            $q = self::chi2Proba(($x-$n)*($x-$n)/(2*$n), 1)/2;
            if($x > $n) {
                return $q;
            }
            return 1 - $q;
        }
        $p = exp(-0.5*$x);
        if(($n%2) == 1){
            $p = $p * sqrt(2*$x/M_PI);
        }
        $k=$n;
        while($k >= 2){
            $p = $p*$x/$k;
            $k = $k - 2;
        }
        $t = $p;
        $a = $n;
        while($t > 0.0000000001*$p){
            $a = $a+2;
            $t = $t*$x/$a;
            $p = $p+$t;
        }
        return 1-$p;
    }
  
      
}// end class

