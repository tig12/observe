<?php
/******************************************************************************
    Astrological computations using tigeph library.
    Input : a csv file with columns containing YYYY-MM-DD dates.
    Output : one csv file per input column, containing longitudes of planets
    
    Based on parameter "actions" of command file ; see self::checkActions() documentation.
    
    @license    GPL
    @history    2021-03-09 04:34:44+01:00, Thierry Graff : Creation from computeAstro1
********************************************************************************/
namespace observe\commands;

use observe\app\Observe;
use observe\app\Config;
use observe\app\Command;
use observe\app\ObserveException;
//use tiglib\arrays\csvAssociative;
use tiglib\arrays\yieldCsvAsso;

use tigeph\Tigeph;
use tigeph\model\SysolC;
use tigeph\model\IAA;
use tigeph\ephem\swetest\Swetest;
use tigeph\ephem\meeus1\Meeus1;

use observe\parts\fileSystem;

class computeAstro implements Command {
    
    /** Astronomical engine used for the computations **/
    private static $engine;
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = __CLASS__;
        //
        // in-file
        if(!isset($params['in-dir'])){
            throw new ObserveException("$classname needs a parameter 'in-dir'");
        }
        if(!isset($params['in-file'])){
            throw new ObserveException("$classname needs a parameter 'in-file'");
        }
        $infile = $params['in-dir'] . DS . $params['in-file'];
        if(!is_file($infile)){
            throw new ObserveException("File not found : $infile");
        }
        //
        // out-dir
        if(!isset($params['out-dir'])){
            throw new ObserveException("$classname needs a parameter 'out-dir'");
        }
        if(!isset($params['out-subdir'])){
            throw new ObserveException("$classname needs a parameter 'out-subdir'");
        }
        $outdir = $params['out-dir'] . DS . $params['out-subdir'];
        fileSystem::mkdir($outdir);
        //
        // has-time
        if(!isset($params['has-time'])){
            throw new ObserveException("$classname needs a parameter 'has-time' (boolean)");
        }
        //
        // astro engines
        $engines = Tigeph::getEngines();
        if(!isset($params['engine'])){
            throw new ObserveException("$classname needs a parameter 'engine' ; supported values: " . implode(', ', $engines));
        }
        if(!in_array($params['engine'], $engines)){
            throw new ObserveException("Invalid parameter 'engine' ({$params['engine']}); supported values: " . implode(', ', $engines));
        }
        self::$engine = $params['engine'];
        //
        // skip
        $skip = false; 
        if(isset($params['skip'])){
            $skip = $params['skip']; // skip = optional parameter
        }
        //
        // actions
        if(!isset($params['actions'])){
            throw new ObserveException("$classname needs a parameter 'actions'");
        }
        $actions = self::checkActions($params['actions']);
        //
        //  initialize tigeph
        //
        if($params['engine'] == 'swetest'){
            Swetest::init(Config::$data['swetest']['bin'], Config::$data['swetest']['dir']);
        }
        //
        //  initialize output
        //
        $outkeys = [];
        $outfiles = [];
        $fps = []; // file pointers
        $outcols = [];
        $emptyCoords = []; // useful when a date = $skip
        foreach($actions as $action){
            $outkey = $action['output'];
            $outkeys[] = $outkey;
            $outfiles[$outkey] = $outdir . DS . $outkey . '.csv';
            $fps[$outkey] = fopen($outfiles[$outkey], 'w');
            foreach($action['compute'] as $planetCode){
                if(!isset($outcols[$outkey])){
                    $outcols[$outkey] = [];
                }
                $outcols[$outkey][] = $planetCode;
            }
            $emptyCoords[$outkey] = array_fill_keys($outcols[$outkey], '');
        }
        foreach($outkeys as $outkey){
            fputcsv($fps[$outkey], $outcols[$outkey], Observe::CSV_SEP);
        }
        //
        //  execute
        //
        $in = yieldCsvAsso::loop($infile);
        //
        $N =0;
        $t1 = microtime(true);
        $emptyNew = array_fill_keys($outkeys, []);
        foreach($in as $line){
            $new = $emptyNew;
            foreach($actions as $action){
                $outkey = $action['output'];
                $date = $line[$action['input']['date']];
                if($date === $skip){
                    $coords = $emptyCoords[$outkey];
                }
                else{
                    $coords = self::ephem(
                        date:       $date,
                        iaaCodes:   $action['tigeph-codes'],
                        hasTime:    $params['has-time'],
                        lg:         $line[$action['input']['lg']],
                        lat:        $line[$action['input']['lat']],
                    );
                }
                foreach($coords as $planetCode => $coord){
                    $new[$outkey][$planetCode] = $coord;
                }
            }
            foreach($outkeys as $outkey){
                fputcsv($fps[$outkey], $new[$outkey], Observe::CSV_SEP);
            }
            $N++;
//if($N == 10) break;
            if($N % 1000 == 0) echo "$N\n";
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        
        foreach($outkeys as $outkey){
            fclose($fps[$outkey]);
            echo "Wrote $N lines in {$outfiles[$outkey]}\n";
        }
        echo "Execution time: $dt s\n";
    }
    
    /**
        Checks actions
        @param  $actions Parsed content of entry "actions" of the command file
                Example of corresponding yaml:
                  actions:
                    -
                      date: C-DATE
                      lg: C-LG
                      lat: C-LAT
                      planets: [SO, MO, ME, VE, MA, JU, SA, UR, NE, PL, NN]
                      generated-file: C
                Meaning:
                - Use column C-DATE of input file and consider that it is the date
                - Use column C-LG of input file and consider that it is the longitude
                - Use column C-LAT of input file and consider that it is the latitude
                - Compute planets SO ... NN
                - Generate file C.csv
        @return Actions checked and completed with default values
    **/
    private static function checkActions($actions){
        $res = [];
        $keys = ['input', 'compute', 'output'];
        $inputKeys = ['date', 'lg', 'lat'];
        foreach($actions as $action){
            $msg = print_r($action, true);
            // check main keys
            foreach($keys as $k){
                if(!isset($action[$k])){
                    throw new ObserveException("In action {$msg}Missing parameter '$k'");
                }
            }
            foreach($action as $k => $v){
                if(!in_array($k, $keys)){
                    throw new ObserveException("In action {$msg}Invalid parameter '$k'");
                }
            }
            // check and complete input
            foreach($action['input'] as $k => $v){
                if(!in_array($k, $inputKeys)){
                    throw new ObserveException("In action {$msg}Invalid parameter '$k'");
                }
            }
            if(!isset($action['input']['date'])){
                throw new ObserveException("In action {$msg}Parameter 'date' is required in 'input' section");
            }
            if(!isset($action['input']['lg'])){
                $action['input']['lg'] = false;
            }
            if(!isset($action['input']['lat'])){
                $action['input']['lat'] = false;
            }
            // check and complete compute
            if(!is_array($action['compute'])){
                throw new ObserveException("In action {$msg}Parameter 'compute' must be an array");
            }
            $action['tigeph-codes'] = [];
            foreach($action['compute'] as $planetCode){
                if(!in_array($planetCode, IAA::PLANETS)){
                    throw new ObserveException("In action {$msg}Invalid planet code '$planetCode'");
                }
                $action['tigeph-codes'][] = IAA::IAA_TIGEPH[$planetCode];
            }
            $res[] = $action;
        }
        return $res;
    }
    
    /** 
        Calls ephemeris computation engine
    **/
    private static function ephem(
        string      $date,
        array       $iaaCodes,
        bool        $hasTime,
        float|bool  $lg = false,
        float|bool  $lat = false,
    ){
        if($hasTime){
            $day_time = $date;
        }
        else {
            $day_time = $date . ' 12:00:00';
        }
        switch(self::$engine){
        	case 'meeus1':  $coords = Meeus1::ephem($day_time, $iaaCodes); break;
        	case 'swetest': $coords = Swetest::ephem($day_time, $iaaCodes); break;
        }
        $res = [];
//echo "\n<pre>"; print_r(IAA::TIGEPH_IAA); echo "</pre>\n"; exit;
        foreach($coords as $iaaCode => $coord){
            $res[IAA::TIGEPH_IAA[$iaaCode]] = $coord;
        }
        return $res;
    }
    
}// end class
