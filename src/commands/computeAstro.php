<?php
/******************************************************************************
    Astrological computations using tigeph library.
    Input : a csv file with columns containing YYYY-MM-DD dates.
    Output : one csv file per input column, containing longitudes of planets
    
    @license    GPL
    @history    2021-03-09 04:34:44+01:00, Thierry Graff : Creation from computeAstro1
********************************************************************************/
namespace observe\commands;

use observe\app\Observe;
use observe\app\Config;
use observe\app\Command;
use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;

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
        $actions = self::computeActions($params['actions']);
        //
        //  initialize tigeph
        //
        if($params['engine'] == 'swetest'){
            Swetest::init(Config::$data['swetest']['bin'], Config::$data['swetest']['dir']);
        }
        //
        //  initialize result, output file names and output columns
        //
        $res = [];
        $outfiles = [];
        $outcols = [];
        $outkeys = []; // = names of output colums
        $emptyCoords = []; // useful when a date = $skip
        foreach($actions as $action){
            $key = $action['in-col'];
            $outkeys[] = $key;
            $res[$key] = [];
            $outfiles[$key] = $outdir . DS . $key . '.csv';
            foreach($action['tigeph-codes'] as $planetCode){
                if(!isset($outcols[$key])){
                    $outcols[$key] = [];
                }
                $outcols[$key][] = IAA::TIGEPH_IAA[$planetCode];
            }
            $emptyCoords[$key] = array_fill_keys($outcols[$key], '');
        }
        foreach($outkeys as $k){
            $res[$k] = implode(Observe::CSV_SEP, $outcols[$k]) . "\n";
        }
        //
        //  execute
        //
        $in = csvAssociative::compute($infile);
        //
        $N =0;
        $t1 = microtime(true);
        $emptyNew = array_fill_keys($outkeys, []);
        foreach($in as $line){
            $new = $emptyNew;
            foreach($actions as $action){
                $currentKey = $action['in-col'];
                $date = $line[$currentKey];
                if($date === $skip){
                    $coords = $emptyCoords[$currentKey];
                }
                else{
                    $coords = self::ephem($date, $action['tigeph-codes']);
                }
                foreach($coords as $planetCode => $coord){
                    $new[$currentKey][$planetCode] = $coord;
                }
            }
            foreach($outkeys as $key){
                $res[$key] .= implode(Observe::CSV_SEP, $new[$key]) . "\n";
            }
            $N++;
            if($N % 1000 == 0) echo "$N\n";
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        
        foreach($outkeys as $key){
            fileSystem::saveFile($outfiles[$key], $res[$key], message: "Wrote $N lines in {$outfiles[$key]}\n");
        }
        echo "Execution time: $dt s\n";
    }
    
    /**
        Parses lines expressing actions, like
            C SO MO ME VE MA JU SA UR NE PL NN SN
        The first word is the name of column of input file (must contain a ISO 8601 date)
        Following words are IAA codes of astrological factors to compute
    **/
    private static function computeActions($param){
        $res = [];
        foreach($param as $line){
            $action = [];
            $tmp = preg_split('/\s+/', $line);
            if(count($tmp) < 2){
                throw new ObserveException("Invalid syntax : $line");
            }
            $action['in-col'] = array_shift($tmp);
            // convert IAA codes to tigeph codes
            $action['tigeph-codes'] = [];
            foreach($tmp as $iaaCode){
                if(!isset(IAA::IAA_TIGEPH[$iaaCode])){
                    throw new ObserveException("Invalid IAA code '$iaaCode' in line : $line");
                }
                $action['tigeph-codes'][] = IAA::IAA_TIGEPH[$iaaCode];
            }
            $res[] = $action;
        }
        return $res;
    }
    
    /** 
        Calls ephemeris computation engine
    **/
    private static function ephem($date, $iaaCodes){
        // TODO compute also time - current code only works for untimed dates
        $day = $date;
        $time = '12:00:00';
        $day_time = "$day $time";
        switch(self::$engine){
        	case 'meeus1':  $coords = Meeus1::ephem($day_time, $iaaCodes); break;
        	case 'swetest': $coords = Swetest::ephem($day_time, $iaaCodes); break;
        }
        $res = [];
        foreach($coords as $iaaCode => $coord){
            $res[IAA::TIGEPH_IAA[$iaaCode]] = $coord;
        }
        return $res;
    }
    
}// end class
