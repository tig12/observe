<?php
/******************************************************************************

    @license    GPL
    @history    2020-12-17 21:31:38+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands;

use observe\Observe;
use observe\Config;
use observe\patterns\Command;
use observe\ObserveException;
use tiglib\arrays\csvAssociative;
use swetest\Sweph;
use swetest\SolarSystemConstants;

class ComputeAstro implements Command {
    
    /** 
        Match between constants used by swetest and
        International Astrological Abbreviations (IAA) for planets,
        as found in journal "Correlation" (vol 30.2 2016)
    **/
    const SWEPH_IAA = [
        SolarSystemConstants::SUN               => 'SO',
        SolarSystemConstants::MOON              => 'MO',
        SolarSystemConstants::MERCURY           => 'ME',
        SolarSystemConstants::VENUS             => 'VE',
        SolarSystemConstants::MARS              => 'MA',
        SolarSystemConstants::JUPITER           => 'JU',
        SolarSystemConstants::SATURN            => 'SA',
        SolarSystemConstants::URANUS            => 'UR',
        SolarSystemConstants::NEPTUNE           => 'NE',
        SolarSystemConstants::PLUTO             => 'PL',
        SolarSystemConstants::MEAN_LUNAR_NODE   => 'NN',
    ];
    
    private static $IAA_SWEPH;
    
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = 'ComputeAstro'; // TODO copute by reflection
        if(!isset($params['input-file'])){
            throw new ObserveException("$classname needs a parameter 'input-file'");
        }
        //
        $infile = $params['input-file'];
        if(!is_file($infile)){
            throw new ObserveException("File not found : $infile");
        }
        //
        if(!isset($params['actions'])){
            throw new ObserveException("$classname needs a parameter 'actions'");
        }
        $actions = self::computeActions($params['actions']);
        //
        if(!isset($params['output-file'])){
            throw new ObserveException("$classname needs a parameter 'output-file'");
        }
        $outfile = $params['output-file'];
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            throw new ObserveException("Create directory '$dir' and try again");
        }
        //
        //  sweph
        //
        self::$IAA_SWEPH = array_flip(self::SWEPH_IAA);
        Sweph::init(Config::$data['swetest']['bin'], Config::$data['swetest']['dir']);
        //
        //  buld output columns
        //
        $outcols = [];
        foreach($actions as $action){
            foreach($action['astro'] as $planetCode){
                $outcols[] = $action['in-col'] . '-' . $planetCode;
            }
        }
        //
        //  execute
        //
        $res = implode(Observe::CSV_SEP, $outcols) . "\n";
        $in = csvAssociative::compute($infile);
        //
        $N =0;
        $t1 = microtime(true);
        foreach($in as $old){
            $new = array_fill_keys($outcols, '');
            foreach($actions as $action){
                $date = $old[$action['in-col']];
                $coords = $action['method']->invoke(null, $date, $action['astro']);
                foreach($coords as $planetCode => $coord){
                    $new[$action['in-col'] . '-' . $planetCode] = $coord;
                }
            }
            $res .= implode(Observe::CSV_SEP, $new) . "\n";
            $N++;
            if($N % 1000 == 0) echo "$N\n";
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        file_put_contents($outfile, $res);
        echo "Wrote $N lines in $outfile ($dt s)\n";
    }
    
    /**
        Parses lines expressing actions, like
            planets C SO MO ME VE MA JU SA UR NE PL NN SN
        The first word is the method name
        The second word is the name of column of input file (ISO 8601 date)
        Following words are IAA codes of astrological factors to compute
    **/
    private static function computeActions($param){
        $res = [];
        foreach($param as $line){
            $action = [];
            $tmp = preg_split('/\s+/', $line);
            if(count($tmp) < 3){
                throw new ObserveException("Invalid syntax : $line");
            }
            $action['method-name'] = array_shift($tmp);
            $action['in-col'] = array_shift($tmp);
            $action['astro'] = $tmp;
            try{
                $method = new \ReflectionMethod(__CLASS__ . '::' . $action['method-name']);
            }
            catch(\ReflectionException $e){
                throw new ObserveException("Invalid method name '{$action['method-name']}' in line : $line");
            }
            $method->setAccessible(true);
            $action['method'] = $method;
            $res[] = $action;
        }
        return $res;
    }
    
    /** 
    **/
    private static function planets($date, $planetCodes){
        $sweDay = substr($date, 8, 2) . '.' . substr($date, 5, 2) . '.' . substr($date, 0, 4);
        $sweTime = '12:00:00';// TODO compute also time
        $sweCodes = array_map(function($code){ return self::$IAA_SWEPH[$code];}, $planetCodes);
        $params = [
            'day'       => $sweDay,
            'time'      => $sweTime,
            'planets'   => $sweCodes,
        ];
        $coords = Sweph::ephem($params);
        $res = [];
        foreach($coords['planets'] as $code => $coord){
            $res[self::SWEPH_IAA[$code]] = $coord;
        }
        return $res;
    }
    
}// end class
