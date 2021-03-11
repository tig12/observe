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

use observe\parts\mfc\distrib\ymd;
use observe\parts\mfc\distrib\planets;
use observe\parts\fileSystem;

class distrib implements Command {
    
    /** Parameters passed to execute(). **/
    private static $params;
    
    private static $dirDistrib;
    
    // ******************************************************
    public static function execute($params=[]) {
        self::$params = $params;
        // skip-W, optional parameter
        if(!isset(self::$params['ymd']['skip-W'])){
            self::$params['ymd']['skip-W'] = false;
        }
        
        self::$dirDistrib = self::$params['out-dir'] . DS . 'distrib';
        
        self::exec_ymd();
        
        self::exec_planets();
        
        self::exec_aspects();
    }
    
    // ******************************************************
    /** Computes distributions using data/ymd.csv **/
    private static function exec_ymd() {
        //
        $inFile = self::$params['in-dir'] . DS . 'data' . DS . 'ymd.csv';
        if(!file_exists($inFile)){
            throw new ObserveException("File $inFile does not exist");
        }
        //
        echo "Computing ymd distributions...\n";
        //
        $data = csvAssociative::compute($inFile);
        $distribs = ymd::computeDistrib(
            data:       $data,
            processW:   self::$params['process-wedding'],
            skipW:      self::$params['ymd']['skip-W'],
        );
        //
        // M
        //
        $outFile = self::$dirDistrib . DS . 'M' . DS . 'year.csv';
        fileSystem::mkdir(dirname($outFile));
        fileSystem::saveFile($outFile, self::distrib2csv($distribs['M']['year']));
        //
        $outFile = self::$dirDistrib . DS . 'M' . DS . 'day.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($distribs['M']['day']));
        //
        if(self::$params['process-wedding']){
            $outFile = self::$dirDistrib . DS . 'M' . DS . 'age-wed.csv';
            fileSystem::saveFile($outFile, self::distrib2csv($distribs['M']['age-wed']));
        }
        //
        $outFile = self::$dirDistrib . DS . 'M' . DS . 'age-child.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($distribs['M']['age-child']));
        //
        // F
        //
        $outFile = self::$dirDistrib . DS . 'F' . DS . 'year.csv';
        fileSystem::mkdir(dirname($outFile));
        fileSystem::saveFile($outFile, self::distrib2csv($distribs['F']['year']));
        //
        $outFile = self::$dirDistrib . DS . 'F' . DS . 'day.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($distribs['F']['day']));
        //
        if(self::$params['process-wedding']){
            $outFile = self::$dirDistrib . DS . 'F' . DS . 'age-wed.csv';
            fileSystem::saveFile($outFile, self::distrib2csv($distribs['F']['age-wed']));
        }
        //
        $outFile = self::$dirDistrib . DS . 'F' . DS . 'age-child.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($distribs['F']['age-child']));
        //
        // C
        //
        $outFile = self::$dirDistrib . DS . 'C' . DS . 'year.csv';
        fileSystem::mkdir(dirname($outFile));
        fileSystem::saveFile($outFile, self::distrib2csv($distribs['C']['year']));
        //
        $outFile = self::$dirDistrib . DS . 'C' . DS . 'day.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($distribs['C']['day']));
        //
        $outFile = self::$dirDistrib . DS . 'C' . DS . 'rank.csv';
        fileSystem::saveFile($outFile, self::distrib2csv($distribs['C']['rank']));
        //
        if(self::$params['process-wedding']){
            $outFile = self::$dirDistrib . DS . 'C' . DS . 'wed-birth.csv';
            fileSystem::saveFile($outFile, self::distrib2csv($distribs['C']['wed-birth']));
        }
        //
        // W
        //
        if(self::$params['process-wedding']){
            $outFile = self::$dirDistrib . DS . 'W' . DS . 'year.csv';
            fileSystem::mkdir(dirname($outFile));
            fileSystem::saveFile($outFile, self::distrib2csv($distribs['W']['year']));
            //
            $outFile = self::$dirDistrib . DS . 'W' . DS . 'day.csv';
            fileSystem::saveFile($outFile, self::distrib2csv($distribs['W']['day']));
            //
            // N is not a distribution but a constant => in txt file
            $outFile = self::$dirDistrib . DS . 'W' . DS . 'N.txt';
            fileSystem::saveFile($outFile, $distribs['W']['N']);
        }
    }
    
    // ******************************************************
    /** Computes distributions of plants of each MFCW member **/
    public static function exec_planets() {
        //
        echo "Computing planet distributions...\n";
        //
        // 1 - load data
        //
        $inDir = self::$params['in-dir'] . DS . 'data' . DS . 'planets';
        $data = [];
        $keys = ['M', 'F', 'C'];
        if(self::$params['process-wedding']){
            $keys[] = 'W';
        }
        foreach($keys as $key){
            $inFile = $inDir . DS . $key . '.csv';
            if(!file_exists($inFile)){
                throw new ObserveException("File $inFile does not exist");
            }
            $data[$key] = csvAssociative::compute($inFile);
        }
        //
        // 2 - compute distributions
        //
        $distribs = planets::computeDistrib(
            data:       $data,
            processW:   self::$params['process-wedding'],
        );
        //
        // 3 - store distributions
        //
        foreach($distribs as $memberKey => $planets){
            $dirname = self::$params['out-dir'] . DS . 'distrib' . DS . $memberKey . DS . 'planets'; // ex distrib/F/planets/
            fileSystem::mkdir($dirname);
            foreach($planets as $planet => $distrib){
                $csv = self::distrib2csv($distrib);
                $filename = $dirname . DS . $planet . '.csv';
                fileSystem::saveFile($filename, $csv);
            }
        }
    }
    
    // ******************************************************
    /** Computes distributions of aspects of each MFCW member **/
    public static function exec_aspects() {
        //
        echo "Computing aspect distributions...\n";
        //
        // 1 - load data
        //
        $inDir = self::$params['in-dir'] . DS . 'data' . DS . 'planets';
        $data = [];
        $keys = ['M', 'F', 'C'];
        if(self::$params['process-wedding']){
            $keys[] = 'W';
        }
        foreach($keys as $key){
            $inFile = $inDir . DS . $key . '.csv';
            if(!file_exists($inFile)){
                throw new ObserveException("File $inFile does not exist");
            }
            $data[$key] = csvAssociative::compute($inFile);
        }
        //
        // 2 - compute distributions
        //
        $distribs = planets::computeDistrib(
            data:       $data,
            processW:   self::$params['process-wedding'],
        );
        //
        // 3 - store distributions
        //
        foreach($distribs as $memberKey => $planets){
            $dirname = self::$params['out-dir'] . DS . 'distrib' . DS . $memberKey . DS . 'planets'; // distrib/F/planets
            fileSystem::mkdir($dirname);
            foreach($planets as $planet => $distrib){
                $csv = self::distrib2csv($distrib);
                $filename = $dirname . DS . $planet . '.csv';
                fileSystem::saveFile($filename, $csv);
            }
        }
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
