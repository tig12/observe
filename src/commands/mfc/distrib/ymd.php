<?php
/******************************************************************************
    Conducts the generation of MFCW distributions using file ymd.csv.
    
    @license    GPL
    @history    2021-03-14 17:59:59+01:00, Thierry Graff : Split to create ymd
    @history    2021-02-14 11:05:05+01:00, Thierry Graff : Creation (commands\mfc\distrib)
********************************************************************************/
namespace observe\commands\mfc\distrib;

use observe\app\Observe;
use observe\app\ObserveException;
use observe\app\Command;
use tiglib\arrays\csvAssociative;
use tiglib\time\diff;

use observe\parts\fileSystem;
use observe\parts\distrib\csvDistrib;

class ymd implements Command {
    
    
    public static function execute($params=[]) {
        // skip-W, optional parameter
        if(!isset($params['skip-W'])){
            $params['skip-W'] = false;
        }
        
        $dirDistrib = $params['out-dir'] . DS . 'distrib';
        
        $inFile = $params['in-dir'] . DS . 'ymd.csv';
        if(!file_exists($inFile)){
            throw new ObserveException("File $inFile does not exist");
        }
        //
        echo "Computing ymd distributions...\n";
        //
        $data = csvAssociative::compute($inFile);
        $distribs = self::computeDistribs(
            data:       $data,
            processW:   $params['experience']['has-wedding'],
            skipW:      $params['skip-W'],
        );
        //
        // M
        //
        $outFile = $dirDistrib . DS . 'M' . DS . 'year.csv';
        fileSystem::mkdir(dirname($outFile));
        fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['M']['year'], Observe::CSV_SEP));
        //
        $outFile = $dirDistrib . DS . 'M' . DS . 'day.csv';
        fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['M']['day'], Observe::CSV_SEP));
        //
        if($params['experience']['has-wedding']){
            $outFile = $dirDistrib . DS . 'M' . DS . 'age-wed.csv';
            fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['M']['age-wed'], Observe::CSV_SEP));
        }
        //
        $outFile = $dirDistrib . DS . 'M' . DS . 'age-child.csv';
        fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['M']['age-child'], Observe::CSV_SEP));
        //
        // F
        //
        $outFile = $dirDistrib . DS . 'F' . DS . 'year.csv';
        fileSystem::mkdir(dirname($outFile));
        fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['F']['year'], Observe::CSV_SEP));
        //
        $outFile = $dirDistrib . DS . 'F' . DS . 'day.csv';
        fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['F']['day'], Observe::CSV_SEP));
        //
        if($params['experience']['has-wedding']){
            $outFile = $dirDistrib . DS . 'F' . DS . 'age-wed.csv';
            fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['F']['age-wed'], Observe::CSV_SEP));
        }
        //
        $outFile = $dirDistrib . DS . 'F' . DS . 'age-child.csv';
        fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['F']['age-child'], Observe::CSV_SEP));
        //
        // C
        //
        $outFile = $dirDistrib . DS . 'C' . DS . 'year.csv';
        fileSystem::mkdir(dirname($outFile));
        fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['C']['year'], Observe::CSV_SEP));
        //
        $outFile = $dirDistrib . DS . 'C' . DS . 'day.csv';
        fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['C']['day'], Observe::CSV_SEP));
        //
        $outFile = $dirDistrib . DS . 'C' . DS . 'rank.csv';
        fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['C']['rank'], Observe::CSV_SEP));
        //
        if($params['experience']['has-wedding']){
            $outFile = $dirDistrib . DS . 'C' . DS . 'wed-birth.csv';
            fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['C']['wed-birth'], Observe::CSV_SEP));
        }
        //
        // W
        //
        if($params['experience']['has-wedding']){
            $outFile = $dirDistrib . DS . 'W' . DS . 'year.csv';
            fileSystem::mkdir(dirname($outFile));
            fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['W']['year'], Observe::CSV_SEP));
            //
            $outFile = $dirDistrib . DS . 'W' . DS . 'day.csv';
            fileSystem::saveFile($outFile, csvDistrib::distrib2csv($distribs['W']['day'], Observe::CSV_SEP));
            //
            // N is not a distribution but a constant => in txt file
            $outFile = $dirDistrib . DS . 'W' . DS . 'N.txt';
            fileSystem::saveFile($outFile, $distribs['W']['N']);
        }
    }
    
    // ******************************************************
    /**
        @param  $data       Array representing a csv file.
                            Each entry is an associative array representing one line of the file.
        @param  $processW   Should wedding distributions be computed ?
        @param  $skipW      When column W has this value, don't include the line in distributions involving W.
                            Useful only if $processW = true.
        @return The YMD distributions in associative arrays.
    **/
    public static function computeDistribs(
        &$data,
        bool $processW,
        $skipW = false,
    ){
        $res = [
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
                'day' => [],
                'delta-mf' => [], // interval [father birth - mother birth] // TODO 
                'N' => 0, // nb of rows with wedding info
            ],
        ];
        if(!$processW){
            unset($res['M']['age-wed']);
            unset($res['F']['age-wed']);
            unset($res['C']['wed-birth']);
            unset($res['W']);
        }
        
        $nW = 0;
        $n = 0;
        
        $lineHasWedding = function($line) use ($skipW) {
            return $line['W'] != $skipW;
        };
        
        foreach($data as $line){
            $n++; 
            [$yM, $mM, $dM] = explode('-', $line['M']);
            [$yF, $mF, $dF] = explode('-', $line['F']);
            [$yC, $mC, $dC] = explode('-', $line['C']);
            $dateM = date_create($line['M']);
            $dateF = date_create($line['F']);
            $dateC = date_create($line['C']);
//            $doy = $dateM->format('z');
            if($processW && $lineHasWedding($line)){
                $nW++;                                                                      
                [$yW, $mW, $dW] = explode('-', $line['W']);
                $dateW = date_create($line['W']);
            }
            //
            // M
            //
            // year
            if(!isset($res['M']['year'][$yM])){ $res['M']['year'][$yM] = 0; }
            $res['M']['year'][$yM]++;
            // day MM-DD
            $doy = "$mM-$dM"; // day of year MM-DD
            if(!isset($res['M']['day'][$doy])){ $res['M']['day'][$doy] = 0; }
            $res['M']['day'][$doy]++;
            // age at wedding
            if($processW && $lineHasWedding($line)){
                $age = diff::compute($dateM, $dateW);
                if(!isset($res['M']['age-wed'][$age])){ $res['M']['age-wed'][$age] = 0; }
                $res['M']['age-wed'][$age]++;
            }
            // age at child birth
            $age = diff::compute($dateM, $dateC);
            if(!isset($res['M']['age-child'][$age])){ $res['M']['age-child'][$age] = 0; }
            $res['M']['age-child'][$age]++;
            //
            // F
            //
            // year
            if(!isset($res['F']['year'][$yF])){ $res['F']['year'][$yF] = 0; }
            $res['F']['year'][$yF]++;
            // day MM-DD
            $doy = "$mF-$dF"; // day of year MM-DD
            if(!isset($res['F']['day'][$doy])){ $res['F']['day'][$doy] = 0; }
            $res['F']['day'][$doy]++;
            // age at wedding
            if($processW && $lineHasWedding($line)){
                $age = diff::compute($dateF, $dateW);
                if(!isset($res['F']['age-wed'][$age])){ $res['F']['age-wed'][$age] = 0; }
                $res['F']['age-wed'][$age]++;
            }
            // age at child birth
            $age = diff::compute($dateF, $dateC);
            if(!isset($res['F']['age-child'][$age])){ $res['F']['age-child'][$age] = 0; }
            $res['F']['age-child'][$age]++;
            //
            // C
            //
            // year
            if(!isset($res['C']['year'][$yC])){ $res['C']['year'][$yC] = 0; }
            $res['C']['year'][$yC]++;
            // day MM-DD
            $doy = "$mC-$dC"; // day of year MM-DD
            if(!isset($res['C']['day'][$doy])){ $res['C']['day'][$doy] = 0; }
            $res['C']['day'][$doy]++;
            // child rank
            $rank = $line['CRANK'];
            if(!isset($res['C']['rank'][$rank])){ $res['C']['rank'][$rank] = 0; }
            $res['C']['rank'][$rank]++;
            // interval wedding - birth
            if($processW && $lineHasWedding($line)){
                $diff = diff::compute($dateW, $dateC, unit:'M');
                if(!isset($res['C']['wed-birth'][$diff])){ $res['C']['wed-birth'][$diff] = 0; }
                $res['C']['wed-birth'][$diff]++;
            }
            //
            // W
            //
            if($processW && $lineHasWedding($line)){
                // N
                $res['W']['N']++;
                // year
                if(!isset($res['W']['year'][$yW])){ $res['W']['year'][$yW] = 0; }
                $res['W']['year'][$yW]++;
                // day MM-DD
                $doy = "$mW-$dW"; // day of year MM-DD
                if(!isset($res['W']['day'][$doy])){ $res['W']['day'][$doy] = 0; }
                $res['W']['day'][$doy]++;
            }
        }
        ksort($res['M']['year']);
        ksort($res['M']['day']);
        ksort($res['M']['age-child']);
        ksort($res['F']['year']);
        ksort($res['F']['day']);
        ksort($res['F']['age-child']);
        ksort($res['C']['year']);
        ksort($res['C']['day']);
        ksort($res['C']['rank']);
        if($processW){
            ksort($res['M']['age-wed']);
            ksort($res['F']['age-wed']);
            ksort($res['C']['wed-birth']);
            ksort($res['W']['year']);
            ksort($res['W']['day']);
        }
        return $res;
    }
    
} // end class
