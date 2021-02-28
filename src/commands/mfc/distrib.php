<?php
/******************************************************************************
    Conducts the generation of distributions
    
    @license    GPL
    @history    2021-02-14 11:05:05+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc;

use observe\app\Observe;
use observe\app\Command;
use observe\app\ObserveException;
use tiglib\arrays\csvAssociative;

use observe\parts\mfc\ymd;
use observe\parts\fileSystem;

class distrib implements Command {
    
    /** Parameters passed to execute() **/
    private static $params;
    
    public static function execute($params=[]){
        
        self::$params = $params;
        
        $dist = [];
        
        //
        // ymd
        //
        $inFile = $params['in-dir'] . DS . $params['ymd']['in-file'];
        if(!file_exists($inFile)){
            throw new ObserveException("File $inFile does not exist");
        }
        $data = csvAssociative::compute($inFile);
        $dist['ymd'] = ymd::loadYMD(
            data:       $data,
            columns:    $params['ymd']['columns'],
            skipW:      $params['ymd']['skip-W'],
        );
//echo "\n<pre>"; print_r($dist['ymd']['W']); echo "</pre>\n"; exit;
        //
        $outBaseDir = $params['out-dir'] . DS . $params['out-subdir'];
        //
        // M
        //
        $outFile = $outBaseDir . DS . 'M' . DS . 'year.csv';
        fileSystem::mkdir(dirname($outFile));
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['M']['year']));
        //
        $outFile = $outBaseDir . DS . 'M' . DS . 'day.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['M']['day']));
        //
        $outFile = $outBaseDir . DS . 'M' . DS . 'age-wed.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['M']['age-wed']));
        //
        $outFile = $outBaseDir . DS . 'M' . DS . 'age-child.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['M']['age-child']));
        //
        // F
        //
        $outFile = $outBaseDir . DS . 'F' . DS . 'year.csv';
        fileSystem::mkdir(dirname($outFile));
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['F']['year']));
        //
        $outFile = $outBaseDir . DS . 'F' . DS . 'day.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['F']['day']));
        //
        $outFile = $outBaseDir . DS . 'F' . DS . 'age-wed.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['F']['age-wed']));
        //
        $outFile = $outBaseDir . DS . 'F' . DS . 'age-child.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['F']['age-child']));
        //
        // C
        //
        $outFile = $outBaseDir . DS . 'C' . DS . 'year.csv';
        fileSystem::mkdir(dirname($outFile));
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['C']['year']));
        //
        $outFile = $outBaseDir . DS . 'C' . DS . 'day.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['C']['day']));
        //
        $outFile = $outBaseDir . DS . 'C' . DS . 'rank.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['C']['rank']));
        //
        $outFile = $outBaseDir . DS . 'C' . DS . 'wed-birth.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['C']['wed-birth']));
        //
        // W
        //
        $outFile = $outBaseDir . DS . 'W' . DS . 'year.csv';
        fileSystem::mkdir(dirname($outFile));
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['W']['year']));
        //
        $outFile = $outBaseDir . DS . 'W' . DS . 'day.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($dist['ymd']['W']['day']));
        //
        // N is not a distribution but a constant => in txt file
        $outFile = $outBaseDir . DS . 'W' . DS . 'N.txt';
        fileSystem::saveFile($outFile, $dist['ymd']['W']['N']);
        //
        //
        // planets
        //
//        $dist['planets'] = planets::loadPlanets($params);
        
        
    }
    
    
    // ******************************************************
    /**
        Builds the content of a csv file containing a distribution
        A distribution is just an associative array $k => $v
    **/
    private static function distrib2csv(&$distrib): string {
        $res = '';
        foreach($distrib as $k => $v){
            $res .= $k . Observe::CSV_SEP . $v . "\n";
        }
        return $res;
    }
    
}// end class
