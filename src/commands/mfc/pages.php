<?php
/******************************************************************************
    Conducts the generation of pages for a MFCW (mother, father, child, mariage) group.
    
    @license    GPL
    @history    2021-02-14 11:05:05+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc;

use observe\app\Observe;
use observe\app\Command;
use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;
use tiglib\time\diff;

use observe\parts\person\Distrib as PersonDistrib;

class pages implements Command {
    
    /** Parameters passed to execute() **/
    private static $params;
    
    /**
        Distributions built from tmp-dir/ymd.csv
    **/
    private static $ymd_distribs = [
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
            'wed-child' => [], // interval [wedding - birth]
        ],
        'W' => [
            'year' => [],
            'n' => 0,
        ],
        
    ];
                                                                           
    public static function execute($params=[]){
        
        self::$params = $params;
        
        self::loadYMD();
        
        $mPage = self::motherFatherPage('M');
        $fPage = self::motherFatherPage('F');
        $cPage = self::childPage();
        $wPage = self::weddingPage();
        
    }
    
    // ******************************************************
    /**
        Computes self::$ymd_distribs
    **/
    public static function loadYMD(){
        
        $colM = self::$params['ymd']['columns']['M'];
        $colF = self::$params['ymd']['columns']['F'];
        $colC = self::$params['ymd']['columns']['C'];
        $colW = self::$params['ymd']['columns']['W'];
        $colCRANK = self::$params['ymd']['columns']['CRANK']; // child rank
        
        $dist =& self::$ymd_distribs;
        
        $nW = 0;
        $n = 0;
        
        // load the file
        $infile = self::$params['tmp-dir'] . DS . self::$params['ymd']['file'];
        $ymd = csvAssociative::compute($infile);
        
        $lineHasWedding = function($line){
            return $line[self::$params['ymd']['columns']['W']] != self::$params['ymd']['skip']['W'];
        };
        
        foreach($ymd as $line){
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
                if(!isset($dist['C']['wed-child'][$diff])){ $dist['C']['wed-child'][$diff] = 0; }
                $dist['C']['wed-child'][$diff]++;
            }
            //
            // W
            //
            if($lineHasWedding($line)){
                // n
                $dist['W']['n']++;
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
        ksort($dist['C']['wed-child']);
        ksort($dist['W']['year']);
        ksort($dist['W']['day']);
//echo "\n<pre>"; print_r(self::$ymd_distribs['W']); echo "</pre>\n"; exit;
    }
    
    // ******************************************************
    /**
        @param $
    **/
    public static function motherFatherPage($MF): string {
        $res = '';
        
//        $distrib = PersonDistrib::yearDistrib(self::$ymd, 'M');
        
        return $res;
    }

    // ******************************************************
    /**
        @param $
    **/
    public static function childPage(): string {
        $res = '';
        return $res;
    }

    // ******************************************************
    /**
        @param $
    **/
    public static function weddingPage(): string {
        $res = '';
        return $res;
    }
    
    
}// end class
